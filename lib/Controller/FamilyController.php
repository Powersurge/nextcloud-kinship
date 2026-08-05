<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Service\FamilyService;
use OCA\Kinship\Service\PermissionService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class FamilyController extends ApiController {
	use ChecksPermissions;

	public function __construct(
		string $appName,
		IRequest $request,
		private FamilyService $service,
		private PermissionService $permissions,
	) {
		parent::__construct(
			$appName,
			$request
		);
	}

	#[NoAdminRequired]
	public function index(): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse([
			'families' => $this->service->getAll()
		]);
	}

	#[NoAdminRequired]
	public function show(
		int $id,
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse(
			$this->service->get($id)
		);
	}

	#[NoAdminRequired]
	public function members(
		int $id,
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse([
			'members' => $this->service->getMembers($id)
		]);
	}

	#[NoAdminRequired]
	public function update(
		int $id,
		array $data,
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_EDIT)) {
			return $denied;
		}

		return new DataResponse(
			$this->service->updateFamily($id, $data)
		);
	}

	#[NoAdminRequired]
	public function delete(
		int $id,
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_DELETE)) {
			return $denied;
		}

		$this->service->deleteFamily($id);

		return new DataResponse(['deleted' => true]);
	}
}
