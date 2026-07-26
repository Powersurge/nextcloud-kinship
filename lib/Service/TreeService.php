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
	 * @param array<int, true> $visited guards against cycles in the graph
	 * @return array<string, mixed>
	 */
	private function buildNode(Person $person, array $visited): array {
		$visited[$person->getId()] = true;
		$children = [];

		foreach ($this->relationshipMapper->findByPerson($person->getId()) as $relationship) {
			if (!RelationshipType::isParenthood($relationship->getType())) {
				continue;
			}
			// Canonical: person_a is the parent. Only descend to this person's
			// own children.
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
			'children' => $children,
		];
	}
}
