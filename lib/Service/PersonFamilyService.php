<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\RelationshipType;
use OCA\Kinship\Db\PersonMapper;
use OCA\Kinship\Db\Relationship;
use OCA\Kinship\Db\RelationshipMapper;

/**
 * Groups a person's stored relationships into the family sidebar sections:
 * active partnerships, ended partnerships, children, and parents.
 */
class PersonFamilyService {
	public function __construct(
		private RelationshipMapper $relationshipMapper,
		private PersonMapper $personMapper,
	) {
	}

	/**
	 * @return array{
	 *     personId: int,
	 *     activePartnerships: list<array<string, mixed>>,
	 *     endedPartnerships: list<array<string, mixed>>,
	 *     children: list<array<string, mixed>>,
	 *     parents: list<array<string, mixed>>,
	 * }
	 */
	public function getForPerson(int $personId): array {
		$relationships = $this->relationshipMapper->findByPerson($personId);
		$namesById = $this->namesForRelatedPeople($relationships, $personId);

		$activePartnerships = [];
		$endedPartnerships = [];
		$children = [];
		$parents = [];

		foreach ($relationships as $relationship) {
			$type = $relationship->getType();

			if (RelationshipType::isActivePartnership($type)) {
				$activePartnerships[] = $this->serializeWithRelated(
					$relationship,
					$this->partnerId($relationship, $personId),
					$namesById,
				);
				continue;
			}

			if (RelationshipType::isEndedPartnership($type)) {
				$endedPartnerships[] = $this->serializeWithRelated(
					$relationship,
					$this->partnerId($relationship, $personId),
					$namesById,
				);
				continue;
			}

			if (!RelationshipType::isParenthood($type)) {
				continue;
			}

			if ($relationship->getPersonA() === $personId) {
				$children[] = $this->serializeWithRelated(
					$relationship,
					$relationship->getPersonB(),
					$namesById,
				);
				continue;
			}

			if ($relationship->getPersonB() === $personId) {
				$parents[] = $this->serializeWithRelated(
					$relationship,
					$relationship->getPersonA(),
					$namesById,
				);
			}
		}

		return [
			'personId' => $personId,
			'activePartnerships' => $activePartnerships,
			'endedPartnerships' => $endedPartnerships,
			'children' => $children,
			'parents' => $parents,
		];
	}

	/**
	 * @param Relationship[] $relationships
	 * @return array<int, string>
	 */
	private function namesForRelatedPeople(array $relationships, int $personId): array {
		$relatedIds = [];

		foreach ($relationships as $relationship) {
			$type = $relationship->getType();

			if (RelationshipType::isPartnership($type)) {
				$relatedIds[] = $this->partnerId($relationship, $personId);
				continue;
			}

			if (!RelationshipType::isParenthood($type)) {
				continue;
			}

			if ($relationship->getPersonA() === $personId) {
				$relatedIds[] = $relationship->getPersonB();
			} elseif ($relationship->getPersonB() === $personId) {
				$relatedIds[] = $relationship->getPersonA();
			}
		}

		$relatedIds = array_values(array_unique($relatedIds));
		if ($relatedIds === []) {
			return [];
		}

		$namesById = [];
		foreach ($this->personMapper->findAll(['selected' => $relatedIds]) as $person) {
			$name = $person->getFullName();
			$namesById[$person->getId()] = $name !== '' ? $name : '#' . $person->getId();
		}

		return $namesById;
	}

	private function partnerId(Relationship $relationship, int $personId): int {
		return $relationship->getPersonA() === $personId
			? $relationship->getPersonB()
			: $relationship->getPersonA();
	}

	/**
	 * @param array<int, string> $namesById
	 * @return array<string, mixed>
	 */
	private function serializeWithRelated(
		Relationship $relationship,
		int $relatedPersonId,
		array $namesById,
	): array {
		$data = $relationship->jsonSerialize();
		$data['relatedPersonId'] = $relatedPersonId;
		$data['relatedPersonName'] = $namesById[$relatedPersonId] ?? ('#' . $relatedPersonId);

		return $data;
	}
}
