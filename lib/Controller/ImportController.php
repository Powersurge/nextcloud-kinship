<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Import\GedcomImporter;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ImportController extends ApiController {

	public function __construct(
		string $appName,
		IRequest $request,
		private GedcomImporter $importer,
	) {
		parent::__construct(
			$appName,
			$request
		);
	}

	/**
	 * Import GEDCOM file.
	 */
	public function gedcom(
		string $content,
	): DataResponse {

		$people
			= $this->importer->import(
				$content
			);

		return new DataResponse([
			'imported' => count($people),

			'people' => $people
		]);

	}

}
