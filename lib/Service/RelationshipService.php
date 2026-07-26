<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\RelationshipType;
use OCA\Kinship\Db\DateValue;
use OCA\Kinship\Db\Relationship;
use OCA\Kinship\Db\RelationshipMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

/**
 * Relationships CRUD. Input types are normalized to a single canonical row
 * (see RelationshipType::canonicalize) so the graph can never hold two
 * contradictory rows for the same pair. Every mutation triggers a rebuild of
 * derived family membership.
 */
class RelationshipService {
	public function __construct(
		private RelationshipMapper $mapper,
		private FamilyMembershipService $familyMembership,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	/**
	 * @return Relationship[]
	 */
	public function getAll(): array {
		return $this->mapper->findAll();
	}

	public function get(int $id): Relationship {
		return $this->mapper->find($id);
	}

	/**
	 * @return Relationship[]
	 */
	public function getForPerson(int $personId): array {
		return $this->mapper->findByPerson($personId);
	}

	/**
	 * Create (or reuse) the canonical relationship for the given triple.
	 */
	public function create(array $data): Relationship {
		$personA = (int)($data['personA'] ?? 0);
		$personB = (int)($data['personB'] ?? 0);
		$type = (string)($data['type'] ?? '');

		if (!RelationshipType::isAcceptedInput($type)) {
			throw new \InvalidArgumentException('Unknown relationship type: ' . $type);
		}

		$canonical = RelationshipType::canonicalize($personA, $personB, $type);
		$date = DateValue::fromArray($data['date'] ?? null);

		$existing = $this->mapper->findCanonical(
			$canonical['personA'],
			$canonical['personB'],
			$canonical['type'],
		);

		if ($existing !== null) {
			// Dedupe: enrich the existing row rather than create a duplicate.
			if (!$date->isEmpty()) {
				$existing->setDate($date);
			}
			if (array_key_exists('note', $data)) {
				$existing->setNote($data['note']);
			}
			$existing->setUpdatedAt($this->timeFactory->getTime());
			$saved = $this->mapper->update($existing);
			$this->familyMembership->rebuildAll();

			return $saved;
		}

		$now = $this->timeFactory->getTime();
		$uid = $this->userSession->getUser()?->getUID();

		$relationship = new Relationship();
		$relationship->setPersonA($canonical['personA']);
		$relationship->setPersonB($canonical['personB']);
		$relationship->setType($canonical['type']);
		//		$relationship->setDate($date);
		$relationship->setNote($data['note'] ?? null);
		$relationship->setOwnerUid($uid);
		$relationship->setCreatedBy($uid);
		$relationship->setCreatedAt($now);
		$relationship->setUpdatedAt($now);

		$saved = $this->mapper->insert($relationship);
		$this->familyMembership->rebuildAll();

		return $saved;
	}

	public function update(int $id, array $data): Relationship {
		$relationship = $this->mapper->find($id);

		if (array_key_exists('date', $data)) {
			$relationship->setDate(DateValue::fromArray($data['date']));
		}
		if (array_key_exists('note', $data)) {
			$relationship->setNote($data['note']);
		}

		$relationship->setUpdatedAt($this->timeFactory->getTime());
		$saved = $this->mapper->update($relationship);

		$this->familyMembership->rebuildAll();

		return $saved;
	}

	public function delete(int $id): void {
		$this->mapper->delete($this->mapper->find($id));
		$this->familyMembership->rebuildAll();
	}
}
