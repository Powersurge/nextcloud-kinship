<?php

declare(strict_types=1);

namespace OCA\Kinship\Import;

use OCA\Kinship\Service\PersonService;

class GedcomImporter {

	public function __construct(
		private GedcomParser $parser,
		private GedcomExtractor $extractor,
		private PersonService $personService,
	) {
	}

	/**
	 * Import GEDCOM file.
	 */
	public function import(
		string $content,
	): array {

		$records
			= $this->parser->parse(
				$content
			);

		$data
			= $this->extractor->extract(
				$records
			);

		return [
			'people'
				=> $data['people'],

			'families'
				=> $data['families']
		];

	}
}
