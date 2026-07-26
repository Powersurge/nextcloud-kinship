<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\RelationshipType;
use OCA\Kinship\Db\Person;
use OCA\Kinship\Db\PersonMapper;
use OCA\Kinship\Db\RelationshipMapper;

/**
 * Builds a descendant tree by walking canonical parenthood relationships
 * (person_a is the parent, person_b the child).
 */
class TreeService {
	public function __construct(
		private PersonMapper $personMapper,
		private RelationshipMapper $relationshipMapper,
	) {
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getTree(int $personId): array {
		$person = $this->personMapper->find($personId);

		return $this->buildNode($person, []);
	}

	/**
	 * @param array<int, true> $visited Guards against cycles.
	 * @return array<string, mixed>
	 */
	private function buildNode(Person $person, array $visited): array {
		$visited[$person->getId()] = true;

		$children = [];
		$spouse = null;

		foreach ($this->relationshipMapper->findByPerson($person->getId()) as $relationship) {
			$type = $relationship->getType();

			// Find spouse
			if ($type === RelationshipType::SPOUSE) {
				$spouseId = $relationship->getPersonA() === $person->getId()
					? $relationship->getPersonB()
					: $relationship->getPersonA();

				$spousePerson = $this->personMapper->findOrNull($spouseId);

				if ($spousePerson !== null) {
					$spouse = [
						'id' => $spousePerson->getId(),
						'gender' => $spousePerson->getGender(),
						'firstName' => $spousePerson->getFirstName(),
						'lastName' => $spousePerson->getLastName(),
					];
				}

				continue;
			}

			// Only descend parent -> child relationships.
			if (!RelationshipType::isParenthood($type)) {
				continue;
			}

			if ($relationship->getPersonA() !== $person->getId()) {
				continue;
			}

			$childId = $relationship->getPersonB();

			if (isset($visited[$childId])) {
				continue;
			}

			$child = $this->personMapper->findOrNull($childId);

			if ($child === null) {
				continue;
			}

			$children[] = $this->buildNode($child, $visited);
		}

		return [
			'id' => $person->getId(),
			'firstName' => $person->getFirstName(),
			'lastName' => $person->getLastName(),
			'gender' => $person->getGender(),
			'spouse' => $spouse,
			'children' => $children,
		];
	}
}
