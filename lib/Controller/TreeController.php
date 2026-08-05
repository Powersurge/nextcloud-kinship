<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Service\PermissionService;
use OCA\Kinship\Service\TreeService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class TreeController extends ApiController {
	use ChecksPermissions;

	public function __construct(
		string $appName,
		IRequest $request,
		private TreeService $service,
		private PermissionService $permissions,
	) {
		parent::__construct(
			$appName,
			$request
		);
	}

	#[NoAdminRequired]
	public function show(
		int $personId,
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse(
			$this->service->getTree(
				$personId
			)
		);
	}
}
