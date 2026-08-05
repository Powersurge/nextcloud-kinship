<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Exception\ValidationException;
use OCA\Kinship\Http\Requests\SourceRequest;
use OCA\Kinship\Service\CitationService;
use OCA\Kinship\Service\PermissionService;
use OCA\Kinship\Service\SourceService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class SourceController extends BaseApiController {
	use ChecksPermissions;

	public function __construct(
		string $appName,
		IRequest $request,
		private SourceService $service,
		private CitationService $citationService,
		private PermissionService $permissions,
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	public function index(): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse(['sources' => $this->service->getAll()]);
	}

	#[NoAdminRequired]
	public function show(int $id): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse($this->service->get($id));
	}

	#[NoAdminRequired]
	public function citations(int $id): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse([
			'citations' => $this->citationService->getForSource($id)
		]);
	}

	#[NoAdminRequired]
	public function create(array $data): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_ADD_SOURCES)) {
			return $denied;
		}

		try {
			$validated = $this->validate($data, SourceRequest::class);
		} catch (ValidationException $e) {
			return $this->validationErrorResponse($e);
		}

		return new DataResponse($this->service->create($validated), Http::STATUS_CREATED);
	}

	#[NoAdminRequired]
	public function update(int $id, array $data): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_EDIT)) {
			return $denied;
		}

		try {
			$validated = $this->validate($data, SourceRequest::class, true);
		} catch (ValidationException $e) {
			return $this->validationErrorResponse($e);
		}

		return new DataResponse($this->service->update($id, $validated));
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
