<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Service\ClaimService;
use OCA\Kinship\Service\PermissionService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ClaimController extends ApiController {
	use ChecksPermissions;

	public function __construct(
		string $appName,
		IRequest $request,
		private ClaimService $service,
		private PermissionService $permissions,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * All claims, optionally filtered by ?status= or ?subjectType=&subjectId=
	 */
	#[NoAdminRequired]
	public function index(?string $status = null, ?string $subjectType = null, ?int $subjectId = null): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		if ($subjectType !== null && $subjectId !== null) {
			$claims = $this->service->getForSubject($subjectType, $subjectId);
		} elseif ($status !== null) {
			$claims = $this->service->getByStatus($status);
		} else {
			$claims = $this->service->getAll();
		}

		return new DataResponse(['claims' => $claims]);
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
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_ADD_CLAIMS)) {
			return $denied;
		}

		return new DataResponse($this->service->create($data), Http::STATUS_CREATED);
	}

	#[NoAdminRequired]
	public function accept(int $id): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_ACCEPT_CLAIMS)) {
			return $denied;
		}

		return new DataResponse($this->service->accept($id));
	}

	#[NoAdminRequired]
	public function reject(int $id): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_ACCEPT_CLAIMS)) {
			return $denied;
		}

		return new DataResponse($this->service->reject($id));
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
