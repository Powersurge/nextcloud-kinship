<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\RelationshipType;
use OCA\Kinship\Constants\SubjectType;
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
		private CitationService $citationService,
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
	 * Optional coParentId creates a matching parent link for the other partner.
	 */
	public function create(array $data): Relationship {
		$saved = $this->persistCreate($data);

		if (array_key_exists('coParentId', $data)) {
			$this->syncCoParent(
				$saved,
				$this->normalizeCoParentId($data['coParentId']),
				$data['date'] ?? null,
			);
		}

		$this->familyMembership->rebuildAll();

		return $saved;
	}

	/**
	 * @return array{personA: int, personB: int, type: string}
	 */
	private function canonicalTriple(int $personA, int $personB, string $type): array {
		if (!RelationshipType::isAcceptedInput($type)) {
			throw new \InvalidArgumentException('Unknown relationship type: ' . $type);
		}

		return RelationshipType::canonicalize($personA, $personB, $type);
	}

	private function normalizeCoParentId(mixed $coParentId): int {
		if ($coParentId === null || $coParentId === '') {
			return 0;
		}

		return (int)$coParentId;
	}

	private function persistCreate(array $data, bool $rebuild = true): Relationship {
		$personA = (int)($data['personA'] ?? 0);
		$personB = (int)($data['personB'] ?? 0);
		$type = (string)($data['type'] ?? '');

		$canonical = $this->canonicalTriple($personA, $personB, $type);
		$date = $data['date'] ?? null;

		if (is_array($date)) {
			$date = DateValue::fromArray($date);
		}

		$existing = $this->mapper->findCanonical(
			$canonical['personA'],
			$canonical['personB'],
			$canonical['type'],
		);

		if ($existing !== null) {
			if (!$date->isEmpty()) {
				$existing->setDate($date);
			}
			if (array_key_exists('note', $data)) {
				$existing->setNote($data['note']);
			}
			$existing->setUpdatedAt($this->timeFactory->getTime());
			$saved = $this->mapper->update($existing);
			if ($rebuild) {
				$this->familyMembership->rebuildAll();
			}

			return $saved;
		}

		$now = $this->timeFactory->getTime();
		$uid = $this->userSession->getUser()?->getUID();

		$relationship = new Relationship();
		$relationship->setPersonA($canonical['personA']);
		$relationship->setPersonB($canonical['personB']);
		$relationship->setType($canonical['type']);
		if (!empty($date)) {
			$relationship->setDate($date);
		}
		$relationship->setNote($data['note'] ?? null);
		$relationship->setOwnerUid($uid);
		$relationship->setCreatedBy($uid);
		$relationship->setCreatedAt($now);
		$relationship->setUpdatedAt($now);

		$saved = $this->mapper->insert($relationship);
		if ($rebuild) {
			$this->familyMembership->rebuildAll();
		}

		return $saved;
	}

	/**
	 * Ensure or remove the co-parent link for a canonical parenthood row.
	 */
	private function syncCoParent(Relationship $primary, int $coParentId, mixed $date): void {
		if (!RelationshipType::isParenthood($primary->getType())) {
			return;
		}

		$parentId = $primary->getPersonA();
		$childId = $primary->getPersonB();

		if ($coParentId > 0 && $coParentId !== $parentId && $coParentId !== $childId) {
			$this->removePartnerCoParentLinks($parentId, $childId, $primary->getType(), $coParentId);
			$this->persistCreate([
				'personA' => $coParentId,
				'personB' => $childId,
				'type' => $primary->getType(),
				'date' => $date,
				'note' => null,
			], rebuild: false);

			return;
		}

		$this->removePartnerCoParentLinks($parentId, $childId, $primary->getType());
	}

	/**
	 * Remove parenthood rows from the primary parent's partners to the child.
	 *
	 * @param int $exceptPartnerId partner to keep; 0 removes all partner co-parent links
	 */
	private function removePartnerCoParentLinks(
		int $parentId,
		int $childId,
		string $type,
		int $exceptPartnerId = 0,
	): void {
		$partnerIds = array_flip($this->partnerIdsOf($parentId));

		foreach ($this->mapper->findByPerson($childId) as $relationship) {
			if ($relationship->getPersonB() !== $childId) {
				continue;
			}

			if ($relationship->getType() !== $type) {
				continue;
			}

			$otherParentId = $relationship->getPersonA();
			if ($otherParentId === $parentId) {
				continue;
			}

			if ($exceptPartnerId > 0 && $otherParentId === $exceptPartnerId) {
				continue;
			}

			if (!isset($partnerIds[$otherParentId])) {
				continue;
			}

			$this->mapper->delete($relationship);
		}
	}

	/**
	 * @return list<int>
	 */
	private function partnerIdsOf(int $personId): array {
		$partners = [];

		foreach ($this->mapper->findByPerson($personId) as $relationship) {
			if (!RelationshipType::isPartnership($relationship->getType())) {
				continue;
			}

			$partners[] = $relationship->getPersonA() === $personId
				? $relationship->getPersonB()
				: $relationship->getPersonA();
		}

		return array_values(array_unique($partners));
	}

	public function update(int $id, array $data): Relationship {
		$relationship = $this->mapper->find($id);

		if (array_key_exists('type', $data)) {
			$type = (string)$data['type'];
			if (!RelationshipType::isAcceptedInput($type)) {
				throw new \InvalidArgumentException('Unknown relationship type: ' . $type);
			}

			$canonical = RelationshipType::canonicalize(
				$relationship->getPersonA(),
				$relationship->getPersonB(),
				$type,
			);

			if (RelationshipType::isPartnership($relationship->getType())) {
				if (!RelationshipType::isPartnership($canonical['type'])) {
					throw new \InvalidArgumentException('Cannot change partnership to a non-partnership type');
				}
				$relationship->setType($canonical['type']);
			}
		}

		if (array_key_exists('date', $data)) {
			$relationship->setDate(DateValue::fromArray($data['date']));
		}
		if (array_key_exists('note', $data)) {
			$relationship->setNote($data['note']);
		}

		$relationship->setUpdatedAt($this->timeFactory->getTime());
		$saved = $this->mapper->update($relationship);

		if (RelationshipType::isParenthood($saved->getType()) && array_key_exists('coParentId', $data)) {
			$this->syncCoParent(
				$saved,
				$this->normalizeCoParentId($data['coParentId']),
				$data['date'] ?? $saved->getDate(),
			);
		}

		$this->familyMembership->rebuildAll();

		return $saved;
	}

	public function delete(int $id): void {
		$relationship = $this->mapper->find($id);
		$this->citationService->deleteBySubject(SubjectType::RELATIONSHIP, $id);
		$this->mapper->delete($relationship);
		$this->familyMembership->rebuildAll();
	}
}
