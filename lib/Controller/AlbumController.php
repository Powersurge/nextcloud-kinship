<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Exception\ValidationException;
use OCA\Kinship\Http\Requests\AlbumRequest;
use OCA\Kinship\Service\AlbumService;
use OCA\Kinship\Service\MediaService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use RuntimeException;

class AlbumController extends BaseApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private AlbumService $service,
		private MediaService $mediaService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Albums for an owner, e.g. ?ownerType=person&ownerId=5
	 */
	public function index(string $ownerType, int $ownerId): DataResponse {
		return new DataResponse([
			'albums' => $this->service->getForOwner($ownerType, $ownerId),
		]);
	}

	public function show(int $id): DataResponse {
		return new DataResponse($this->service->get($id));
	}

	public function media(int $id): DataResponse {
		return new DataResponse([
			'media' => $this->mediaService->getForAlbum($id),
		]);
	}

	public function create(array $data): DataResponse {
		try {
			$validated = $this->validate($data, AlbumRequest::class);
			return new DataResponse($this->service->create($validated), Http::STATUS_CREATED);
		} catch (ValidationException $e) {
			return $this->validationErrorResponse($e);
		} catch (RuntimeException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
	}

	public function update(int $id, array $data): DataResponse {
		try {
			$validated = $this->validate($data, AlbumRequest::class, true);
			return new DataResponse($this->service->update($id, $validated));
		} catch (ValidationException $e) {
			return $this->validationErrorResponse($e);
		} catch (RuntimeException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
	}

	public function delete(int $id): DataResponse {
		$this->service->delete($id);

		return new DataResponse(['deleted' => true]);
	}
}
