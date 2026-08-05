<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Exception\ValidationException;
use OCA\Kinship\Http\Requests\CitationRequest;
use OCA\Kinship\Service\CitationService;
use OCA\Kinship\Service\PermissionService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class CitationController extends BaseApiController {
	use ChecksPermissions;

	public function __construct(
		string $appName,
		IRequest $request,
		private CitationService $service,
		private PermissionService $permissions,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Citations for a subject, e.g. ?subjectType=person&subjectId=5
	 */
	#[NoAdminRequired]
	public function index(string $subjectType, int $subjectId): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse([
			'citations' => $this->service->getForSubject($subjectType, $subjectId)
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
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_ADD_SOURCES)) {
			return $denied;
		}

		try {
			$validated = $this->validate($data, CitationRequest::class);
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
			$validated = $this->validate($data, CitationRequest::class, true);
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
