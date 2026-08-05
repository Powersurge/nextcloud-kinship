<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Service\CustomEventService;
use OCA\Kinship\Service\PermissionService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class CustomEventController extends ApiController {
	use ChecksPermissions;

	public function __construct(
		string $appName,
		IRequest $request,
		private CustomEventService $service,
		private PermissionService $permissions,
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	public function index(): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse(['customEvents' => $this->service->getAll()]);
	}

	#[NoAdminRequired]
	public function show(int $id): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse($this->service->get($id));
	}

	#[NoAdminRequired]
	public function create(array $data): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_MANAGE_CUSTOM_EVENTS)) {
			return $denied;
		}

		return new DataResponse($this->service->create($data), Http::STATUS_CREATED);
	}

	#[NoAdminRequired]
	public function update(int $id, array $data): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_MANAGE_CUSTOM_EVENTS)) {
			return $denied;
		}

		return new DataResponse($this->service->update($id, $data));
	}

	#[NoAdminRequired]
	public function delete(int $id): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_MANAGE_CUSTOM_EVENTS)) {
			return $denied;
		}

		$this->service->delete($id);

		return new DataResponse(['deleted' => true]);
	}
}
