<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Import\GedcomImporter;
use OCA\Kinship\Service\PermissionService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ImportController extends ApiController {
	use ChecksPermissions;

	public function __construct(
		string $appName,
		IRequest $request,
		private GedcomImporter $gedcomImporter,
		private PermissionService $permissions,
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
	#[NoAdminRequired]
	public function gedcom(string $content): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_EDIT)) {
			return $denied;
		}

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
