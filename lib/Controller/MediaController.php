<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Exception\ValidationException;
use OCA\Kinship\Http\Requests\MediaRequest;
use OCA\Kinship\Service\FilesService;
use OCA\Kinship\Service\MediaService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use RuntimeException;

class MediaController extends BaseApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private MediaService $service,
		private FilesService $filesService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Media references for a subject, e.g. ?subjectType=person&subjectId=5
	 * Optional albumId: omit = all; 0 = loose only; >0 = that album.
	 */
	public function index(string $subjectType, int $subjectId): DataResponse {
		$albumParam = $this->request->getParam('albumId');
		$albumId = null;
		if ($albumParam !== null && $albumParam !== '') {
			$albumId = (int)$albumParam;
		}

		return new DataResponse([
			'media' => $this->service->getForSubject($subjectType, $subjectId, $albumId),
		]);
	}

	public function show(int $id): DataResponse {
		return new DataResponse($this->service->get($id));
	}

	public function create(array $data): DataResponse {
		try {
			$validated = $this->validate($data, MediaRequest::class);
			return new DataResponse($this->service->create($validated), Http::STATUS_CREATED);
		} catch (ValidationException $e) {
			return $this->validationErrorResponse($e);
		} catch (RuntimeException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * Upload a file into Kinship/Uploads and attach it as media.
	 */
	#[NoCSRFRequired]
	public function upload(): DataResponse {
		$uploaded = $this->request->getUploadedFile('file');
		if ($uploaded === null || !isset($uploaded['tmp_name'], $uploaded['name'])) {
			return new DataResponse(['error' => 'File is required'], Http::STATUS_BAD_REQUEST);
		}

		if (($uploaded['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
			return new DataResponse(['error' => 'Upload failed'], Http::STATUS_BAD_REQUEST);
		}

		$subjectType = (string)$this->request->getParam('subjectType', '');
		$subjectId = (int)$this->request->getParam('subjectId', 0);
		$albumParam = $this->request->getParam('albumId');
		$caption = $this->request->getParam('caption');
		$isPrimaryRaw = $this->request->getParam('isPrimary');

		$data = [
			'subjectType' => $subjectType,
			'subjectId' => $subjectId,
			'caption' => is_string($caption) ? $caption : null,
			'isPrimary' => filter_var($isPrimaryRaw, FILTER_VALIDATE_BOOLEAN),
		];
		if ($albumParam !== null && $albumParam !== '') {
			$data['albumId'] = (int)$albumParam;
		}

		try {
			$content = file_get_contents($uploaded['tmp_name']);
			if ($content === false) {
				return new DataResponse(['error' => 'Could not read uploaded file'], Http::STATUS_BAD_REQUEST);
			}

			$fileId = $this->filesService->uploadToInbox((string)$uploaded['name'], $content);
			$data['fileId'] = $fileId;

			$validated = $this->validate($data, MediaRequest::class);
			return new DataResponse($this->service->create($validated), Http::STATUS_CREATED);
		} catch (ValidationException $e) {
			return $this->validationErrorResponse($e);
		} catch (RuntimeException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
	}

	public function update(int $id, array $data): DataResponse {
		try {
			$validated = $this->validate($data, MediaRequest::class, true);
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
