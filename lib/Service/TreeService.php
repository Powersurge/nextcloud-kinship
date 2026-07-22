<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Db\PersonMapper;
use OCA\Kinship\Db\RelationshipMapper;

class TreeService {
	public function __construct(
		private PersonMapper $personMapper,
		private RelationshipMapper $relationshipMapper,
	) {
	}

	/**
	 * Build family tree.
	 */
	public function getTree(
		int $personId,
	): array {

		$person
			= $this->personMapper->find(
				$personId
			);

		return $this->buildNode(
			$person
		);

	}

	private function buildNode(
		$person,
	): array {

		$children = [];

		$relationships
			= $this->relationshipMapper
				->findByPerson(
					$person->getId()
				);

		foreach ($relationships as $relationship) {

			if ($relationship->getType() !== 'parent') {
				continue;
			}

			$child
				= $this->personMapper->find(
					$relationship->getPersonB()
				);

			$children[]
				= $this->buildNode(
					$child
				);

		}

		return [
			'id'
				=> $person->getId(),

			'firstName'
				=> $person->getFirstName(),

			'lastName'
				=> $person->getLastName(),

			'children'
				=> $children
		];

	}

}
