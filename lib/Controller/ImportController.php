<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Import\GedcomImporter;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ImportController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private GedcomImporter $gedcomImporter,
	) {
		parent::__construct(
			$appName,
			$request
		);
	}

	/**
	 * Import a GEDCOM file from raw text content posted by the client.
	 *
	 */
	#[NoCSRFRequired]
	public function gedcom(string $content): DataResponse {
		if (trim($content) === '') {
			return new DataResponse(
				['error' => 'GEDCOM content is required'],
				Http::STATUS_BAD_REQUEST,
			);
		}

		try {
			$result = $this->gedcomImporter->import($content);

			return new DataResponse($result);
		} catch (\Exception $e) {
			return new DataResponse(
				['error' => $e->getMessage()],
				Http::STATUS_BAD_REQUEST,
			);
		} catch (\Throwable $e) {
			return new DataResponse(
				['error' => $e->getMessage()],
				Http::STATUS_INTERNAL_SERVER_ERROR,
			);
		}
	}
}
