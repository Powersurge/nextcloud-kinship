<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Service\FactService;
use OCA\Kinship\Service\PermissionService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class FactController extends ApiController {
	use ChecksPermissions;

	public function __construct(
		string $appName,
		IRequest $request,
		private FactService $service,
		private PermissionService $permissions,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * List facts for a subject, e.g. /api/v1/facts?subjectType=person&subjectId=5
	 */
	#[NoAdminRequired]
	public function index(string $subjectType, int $subjectId): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse([
			'facts' => $this->service->getForSubject($subjectType, $subjectId)
		]);
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
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_EDIT)) {
			return $denied;
		}

		return new DataResponse($this->service->create($data), Http::STATUS_CREATED);
	}

	#[NoAdminRequired]
	public function update(int $id, array $data): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_EDIT)) {
			return $denied;
		}

		return new DataResponse($this->service->update($id, $data));
	}

	#[NoAdminRequired]
	public function delete(int $id): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_DELETE)) {
			return $denied;
		}

		$this->service->delete($id);

		return new DataResponse(['deleted' => true]);
	}
}
