<?php

declare(strict_types=1);

namespace OCA\Kinship\Import;

use OCA\Kinship\Db\Family;
use OCA\Kinship\Db\FamilyMapper;

class GedcomFamilyImporter {

	public function __construct(
		private FamilyMapper $mapper,
	) {
	}

	public function create(
		string $name,
	): Family {

		$family
			= new Family();

		$family->setName(
			$name
		);

		return $this->mapper->insert(
			$family
		);

	}

}
