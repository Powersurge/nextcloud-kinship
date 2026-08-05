<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Exception\ValidationException;
use OCA\Kinship\Http\Requests\AlbumRequest;
use OCA\Kinship\Service\AlbumService;
use OCA\Kinship\Service\MediaService;
use OCA\Kinship\Service\PermissionService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use RuntimeException;

class AlbumController extends BaseApiController {
	use ChecksPermissions;

	public function __construct(
		string $appName,
		IRequest $request,
		private AlbumService $service,
		private MediaService $mediaService,
		private PermissionService $permissions,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Albums for an owner, e.g. ?ownerType=person&ownerId=5
	 */
	#[NoAdminRequired]
	public function index(string $ownerType, int $ownerId): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse([
			'albums' => $this->service->getForOwner($ownerType, $ownerId),
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
	public function media(int $id): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse([
			'media' => $this->mediaService->getForAlbum($id),
		]);
	}

	#[NoAdminRequired]
	public function create(array $data): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_EDIT)) {
			return $denied;
		}

		try {
			$validated = $this->validate($data, AlbumRequest::class);
			return new DataResponse($this->service->create($validated), Http::STATUS_CREATED);
		} catch (ValidationException $e) {
			return $this->validationErrorResponse($e);
		} catch (RuntimeException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
	}

	#[NoAdminRequired]
	public function update(int $id, array $data): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_EDIT)) {
			return $denied;
		}

		try {
			$validated = $this->validate($data, AlbumRequest::class, true);
			return new DataResponse($this->service->update($id, $validated));
		} catch (ValidationException $e) {
			return $this->validationErrorResponse($e);
		} catch (RuntimeException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
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
