<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\EventType;
use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Service\EventService;
use OCA\Kinship\Service\PermissionService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class EventController extends ApiController {
	use ChecksPermissions;

	public function __construct(
		string $appName,
		IRequest $request,
		private EventService $service,
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
			'events' => $this->service->getAll()
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
	public function personEvents(
		int $personId,
		int $limit = 10,
		int $offset = 0,
		?string $type = null,
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		if ($type !== null && !EventType::isValid($type)) {
			$limit = max(1, min(50, $limit));
			$offset = max(0, $offset);

			return new DataResponse([
				'events' => [],
				'total' => 0,
				'limit' => $limit,
				'offset' => $offset,
			]);
		}

		return new DataResponse(
			$this->service->getForPersonPaginated($personId, $limit, $offset, $type)
		);
	}

	#[NoAdminRequired]
	public function familyEvents(
		int $familyId,
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse([
			'events' => $this->service->getForFamily($familyId)
		]);
	}

	#[NoAdminRequired]
	public function participants(
		int $id,
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse([
			'participants' => $this->service->getParticipants($id)
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
			$this->service->updateEvent($id, $data)
		);
	}

	#[NoAdminRequired]
	public function delete(
		int $id,
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_DELETE)) {
			return $denied;
		}

		$this->service->deleteEvent($id);

		return new DataResponse(['deleted' => true]);
	}
}
