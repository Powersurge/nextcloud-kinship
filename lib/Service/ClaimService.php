<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\ClaimStatus;
use OCA\Kinship\Db\Claim;
use OCA\Kinship\Db\ClaimMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

/**
 * Proposed changes pending review. v0.1 records the proposal and its
 * pending/accepted/rejected state; automatically applying an accepted claim to
 * its target is a later (v0.3) concern.
 */
class ClaimService {
	public function __construct(
		private ClaimMapper $mapper,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	/**
	 * @return Claim[]
	 */
	public function getAll(): array {
		return $this->mapper->findAll();
	}

	public function get(int $id): Claim {
		return $this->mapper->find($id);
	}

	/**
	 * @return Claim[]
	 */
	public function getForSubject(string $subjectType, int $subjectId): array {
		return $this->mapper->findBySubject($subjectType, $subjectId);
	}

	/**
	 * @return Claim[]
	 */
	public function getByStatus(string $status): array {
		return $this->mapper->findByStatus($status);
	}

	public function create(array $data): Claim {
		$now = $this->timeFactory->getTime();

		$operation = $data['operation'] ?? ClaimStatus::OP_UPDATE;
		if (!ClaimStatus::isValidOperation($operation)) {
			$operation = ClaimStatus::OP_UPDATE;
		}

		$proposed = $data['proposedValue'] ?? null;
		if (is_array($proposed)) {
			$proposed = json_encode($proposed);
		}

		$claim = new Claim();
		$claim->setSubjectType($data['subjectType'] ?? '');
		$claim->setSubjectId(isset($data['subjectId']) ? (int)$data['subjectId'] : null);
		$claim->setField($data['field'] ?? null);
		$claim->setProposedValue($proposed);
		$claim->setOperation($operation);
		$claim->setStatus(ClaimStatus::PENDING);
		$claim->setConfidence($data['confidence'] ?? null);
		$claim->setRationale($data['rationale'] ?? null);
		$claim->setSourceId(isset($data['sourceId']) ? (int)$data['sourceId'] : null);
		$claim->setCreatedBy($this->userSession->getUser()?->getUID());
		$claim->setCreatedAt($now);
		$claim->setUpdatedAt($now);

		return $this->mapper->insert($claim);
	}

	public function accept(int $id): Claim {
		return $this->resolve($id, ClaimStatus::ACCEPTED);
	}

	public function reject(int $id): Claim {
		return $this->resolve($id, ClaimStatus::REJECTED);
	}

	private function resolve(int $id, string $status): Claim {
		$claim = $this->mapper->find($id);
		$now = $this->timeFactory->getTime();

		$claim->setStatus($status);
		$claim->setReviewedBy($this->userSession->getUser()?->getUID());
		$claim->setReviewedAt($now);
		$claim->setUpdatedAt($now);

		return $this->mapper->update($claim);
	}

	public function delete(int $id): void {
		$this->mapper->delete($this->mapper->find($id));
	}
}
