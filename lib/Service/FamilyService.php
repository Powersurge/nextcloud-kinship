<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Db\Family;
use OCA\Kinship\Db\FamilyMapper;
use OCA\Kinship\Db\FamilyMember;
use OCA\Kinship\Db\FamilyMemberMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

/**
 * Families CRUD. Membership is derived from relationships
 * (see FamilyMembershipService); families here carry only shared, family-level
 * information (name, notes). Manually created families are marked is_derived =
 * false so the membership rebuild never removes them.
 */
class FamilyService {
	public function __construct(
		private FamilyMapper $mapper,
		private FamilyMemberMapper $memberMapper,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	/**
	 * @return Family[]
	 */
	public function getAll(): array {
		return $this->mapper->findAll();
	}

	public function get(int $id): Family {
		return $this->mapper->find($id);
	}

	/**
	 * @return FamilyMember[]
	 */
	public function getMembers(int $familyId): array {
		return $this->memberMapper->findByFamily($familyId);
	}

	public function create(array $data): Family {
		$now = $this->timeFactory->getTime();
		$uid = $this->userSession->getUser()?->getUID();

		$family = new Family();
		$family->setName($data['name'] ?? null);
		$family->setType($data['type'] ?? null);
		$family->setDescription($data['description'] ?? null);
		$family->setIsDerived(false);
		$family->setOwnerUid($uid);
		$family->setCreatedBy($uid);
		$family->setCreatedAt($now);
		$family->setUpdatedAt($now);

		return $this->mapper->insert($family);
	}

	public function updateFamily(int $id, array $data): Family {
		$family = $this->mapper->find($id);

		if (array_key_exists('name', $data)) {
			$family->setName($data['name']);
		}
		if (array_key_exists('type', $data)) {
			$family->setType($data['type']);
		}
		if (array_key_exists('description', $data)) {
			$family->setDescription($data['description']);
		}

		$family->setUpdatedAt($this->timeFactory->getTime());

		return $this->mapper->update($family);
	}

	public function deleteFamily(int $id): void {
		$family = $this->mapper->find($id);
		$this->memberMapper->deleteByFamily($id);
		$this->mapper->delete($family);
	}
}
