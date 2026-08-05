<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Service\PermissionService;
use OCA\Kinship\Service\RelationshipGraphService;
use OCA\Kinship\Service\RelationshipService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class RelationshipController extends ApiController {
	use ChecksPermissions;

	public function __construct(
		string $appName,
		IRequest $request,
		private RelationshipService $service,
		private RelationshipGraphService $graphService,
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
			'relationships' => $this->service->getAll()
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

	/**
	 * Stored (canonical) relationships for a person.
	 */
	#[NoAdminRequired]
	public function personRelationships(
		int $personId,
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse([
			'relationships' => $this->service->getForPerson($personId)
		]);
	}

	/**
	 * Derived (computed) relationships for a person.
	 */
	#[NoAdminRequired]
	public function personDerived(
		int $personId,
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse([
			'derived' => $this->graphService->getDerived($personId)
		]);
	}

	#[NoAdminRequired]
	public function create(
		array $data,
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_EDIT)) {
			return $denied;
		}

		return new DataResponse(
			$this->service->create($data),
			Http::STATUS_CREATED
		);
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
			$this->service->update($id, $data)
		);
	}

	#[NoAdminRequired]
	public function delete(
		int $id,
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_DELETE)) {
			return $denied;
		}

		$this->service->delete($id);

		return new DataResponse(['deleted' => true]);
	}
}
