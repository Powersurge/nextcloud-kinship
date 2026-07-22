<?php

declare(strict_types=1);

namespace OCA\Kinship\Import;

use OCA\Kinship\Db\Relationship;
use OCA\Kinship\Db\RelationshipMapper;

class GedcomRelationshipImporter {

	public function __construct(
		private RelationshipMapper $mapper,
	) {
	}

	public function create(
		int $personA,
		int $personB,
		string $type,
	): Relationship {

		$relationship
			= new Relationship();

		$relationship->setPersonA(
			$personA
		);

		$relationship->setPersonB(
			$personB
		);

		$relationship->setType(
			$type
		);

		return $this->mapper->insert(
			$relationship
		);

	}

}
