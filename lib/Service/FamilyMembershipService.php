<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\RelationshipType;
use OCA\Kinship\Db\Family;
use OCA\Kinship\Db\FamilyMapper;
use OCA\Kinship\Db\FamilyMember;
use OCA\Kinship\Db\FamilyMemberMapper;
use OCA\Kinship\Db\PersonMapper;
use OCA\Kinship\Db\RelationshipMapper;
use OCP\AppFramework\Utility\ITimeFactory;

/**
 * Derives family membership from the relationship graph.
 *
 * Relationships are the source of truth. A partnership forms a family unit;
 * its children are those linked as children of both partners. A parent with
 * children but no partner forms a single_parent family.
 *
 * On every change the whole set is rebuilt and reconciled against existing
 * families by their parent signature, so user-entered family-level data (name,
 * notes, media) on a still-valid family is preserved: only the derived
 * membership rows are rewritten, and only genuinely stale derived families are
 * removed.
 */
class FamilyMembershipService {
	public function __construct(
		private RelationshipMapper $relationshipMapper,
		private FamilyMapper $familyMapper,
		private FamilyMemberMapper $memberMapper,
		private PersonMapper $personMapper,
		private ITimeFactory $timeFactory,
	) {
	}

	/**
	 * Recompute every derived family from the current relationship graph.
	 */
	public function rebuildAll(): void {
		$relationships = $this->relationshipMapper->findAll();

		/** @var array<int, array<int, true>> $parentsOfChild childId => set of parentId */
		$parentsOfChild = [];
		/** @var array<string, array{0: int, 1: int}> $partnerships signature => [low, high] */
		$partnerships = [];

		foreach ($relationships as $rel) {
			$type = $rel->getType();

			if (RelationshipType::isParenthood($type)) {
				$parentsOfChild[$rel->getPersonB()][$rel->getPersonA()] = true;
				continue;
			}

			if (RelationshipType::isSymmetric($type)) {
				$low = min($rel->getPersonA(), $rel->getPersonB());
				$high = max($rel->getPersonA(), $rel->getPersonB());
				$partnerships[$low . '-' . $high] = [$low, $high];
			}
		}

		// children grouped by each parent
		/** @var array<int, array<int, true>> $childrenOfParent */
		$childrenOfParent = [];
		foreach ($parentsOfChild as $childId => $parents) {
			foreach (array_keys($parents) as $parentId) {
				$childrenOfParent[$parentId][$childId] = true;
			}
		}

		$units = $this->buildUnits($partnerships, $childrenOfParent);

		$this->reconcile($units);
	}

	/**
	 * @param array<string, array{0: int, 1: int}> $partnerships
	 * @param array<int, array<int, true>> $childrenOfParent
	 * @return list<array{parents: list<int>, children: list<int>, type: string}>
	 */
	private function buildUnits(array $partnerships, array $childrenOfParent): array {
		$units = [];
		$partneredParents = [];

		foreach ($partnerships as [$p1, $p2]) {
			$partneredParents[$p1] = true;
			$partneredParents[$p2] = true;

			// Child belongs to a partnership family only when both partners are parents.
			$children = array_intersect_key(
				$childrenOfParent[$p1] ?? [],
				$childrenOfParent[$p2] ?? [],
			);
			$childIds = array_map('intval', array_keys($children));
			sort($childIds);

			$parents = [$p1, $p2];
			sort($parents);

			$units[] = [
				'parents' => $parents,
				'children' => $childIds,
				'type' => 'partnership',
			];
		}

		// Single parents: have children but are not part of any partnership.
		foreach ($childrenOfParent as $parentId => $children) {
			if (isset($partneredParents[$parentId])) {
				continue;
			}

			$childIds = array_map('intval', array_keys($children));
			sort($childIds);

			$units[] = [
				'parents' => [$parentId],
				'children' => $childIds,
				'type' => 'single_parent',
			];
		}

		return $units;
	}

	/**
	 * @param list<array{parents: list<int>, children: list<int>, type: string}> $units
	 */
	private function reconcile(array $units): void {
		$existing = $this->familyMapper->findAll();

		/** @var array<string, Family> $derivedBySignature */
		$derivedBySignature = [];
		foreach ($existing as $family) {
			if (!$family->getIsDerived()) {
				continue;
			}
			$signature = $this->signatureOf($this->parentIdsOf($family->getId()));
			// Keep the first family for a signature; extras become stale.
			$derivedBySignature[$signature] ??= $family;
		}

		$matched = [];

		foreach ($units as $unit) {
			$signature = $this->signatureOf($unit['parents']);
			$family = $derivedBySignature[$signature] ?? null;

			if ($family === null) {
				$family = $this->createDerivedFamily($unit);
			} else {
				// Refresh the auto type/name only if the family still looks auto.
				if ($family->getName() === null || $family->getName() === '') {
					$family->setName($this->buildName($unit['parents']));
				}
				$family->setType($unit['type']);
				$family->setUpdatedAt($this->timeFactory->getTime());
				$this->familyMapper->update($family);
			}

			$matched[$family->getId()] = true;
			$this->syncMembers($family->getId(), $unit['parents'], $unit['children']);
		}

		// Remove derived families that no longer correspond to any unit.
		foreach ($existing as $family) {
			if (!$family->getIsDerived() || isset($matched[$family->getId()])) {
				continue;
			}
			$this->memberMapper->deleteByFamily($family->getId());
			$this->familyMapper->delete($family);
		}
	}

	/**
	 * @param array{parents: list<int>, children: list<int>, type: string} $unit
	 */
	private function createDerivedFamily(array $unit): Family {
		$now = $this->timeFactory->getTime();

		$family = new Family();
		$family->setName($this->buildName($unit['parents']));
		$family->setType($unit['type']);
		$family->setIsDerived(true);
		$family->setCreatedAt($now);
		$family->setUpdatedAt($now);

		return $this->familyMapper->insert($family);
	}

	/**
	 * @param list<int> $parents
	 * @param list<int> $children
	 */
	private function syncMembers(int $familyId, array $parents, array $children): void {
		$this->memberMapper->deleteByFamily($familyId);
		$now = $this->timeFactory->getTime();

		foreach ($parents as $personId) {
			$this->addMember($familyId, $personId, FamilyMember::ROLE_PARENT, $now);
		}
		foreach ($children as $personId) {
			$this->addMember($familyId, $personId, FamilyMember::ROLE_CHILD, $now);
		}
	}

	private function addMember(int $familyId, int $personId, string $role, int $now): void {
		$member = new FamilyMember();
		$member->setFamilyId($familyId);
		$member->setPersonId($personId);
		$member->setRole($role);
		$member->setCreatedAt($now);
		$this->memberMapper->insert($member);
	}

	/**
	 * @return list<int> sorted parent person ids of a family
	 */
	private function parentIdsOf(int $familyId): array {
		$parents = [];
		foreach ($this->memberMapper->findByFamily($familyId) as $member) {
			if ($member->getRole() === FamilyMember::ROLE_PARENT) {
				$parents[] = $member->getPersonId();
			}
		}
		sort($parents);

		return $parents;
	}

	/**
	 * @param list<int> $parentIds
	 */
	private function signatureOf(array $parentIds): string {
		$sorted = $parentIds;
		sort($sorted);

		return implode('-', $sorted);
	}

	/**
	 * @param list<int> $parents
	 */
	private function buildName(array $parents): string {
		$names = [];
		foreach ($parents as $personId) {
			$person = $this->personMapper->findOrNull($personId);
			if ($person === null) {
				continue;
			}
			$names[] = trim($person->getFirstName() . ' ' . $person->getLastName());
		}

		if ($names === []) {
			return 'Family';
		}

		return implode(' & ', $names);
	}
}
