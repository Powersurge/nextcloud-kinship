<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Db\MediaMapper;
use OCA\Kinship\Db\PersonMapper;
use OCA\Kinship\Service\FilesService;
use OCA\Kinship\Service\PermissionService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\StreamResponse;
use OCP\Files\NotFoundException;
use OCP\IPreview;
use OCP\IRequest;

/**
 * Proxy preview/download for Kinship-attached files so viewers without
 * Nextcloud file ACL can still see genealogy media.
 */
class MediaFileController extends Controller {
	use ChecksPermissions;

	public function __construct(
		string $appName,
		IRequest $request,
		private PermissionService $permissions,
		private FilesService $filesService,
		private MediaMapper $mediaMapper,
		private PersonMapper $personMapper,
		private IPreview $previewManager,
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function preview(
		int $fileId,
		int $x = 256,
		int $y = 256,
		bool $a = true,
	): FileDisplayResponse|DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		if ($fileId < 1 || $x < 1 || $y < 1) {
			return new DataResponse(['error' => 'Invalid preview request'], Http::STATUS_BAD_REQUEST);
		}

		$ownerUid = $this->resolveOwnerUid($fileId);
		if ($ownerUid === null) {
			return new DataResponse(['error' => 'File not found'], Http::STATUS_NOT_FOUND);
		}

		$file = $this->filesService->getFileForOwner($fileId, $ownerUid);
		if ($file === null) {
			return new DataResponse(['error' => 'File not found'], Http::STATUS_NOT_FOUND);
		}

		try {
			$preview = $this->previewManager->getPreview(
				$file,
				$x,
				$y,
				!$a,
			);
			$response = new FileDisplayResponse(
				$preview,
				Http::STATUS_OK,
				['Content-Type' => $preview->getMimeType()],
			);
			$response->cacheFor(3600 * 24);

			return $response;
		} catch (NotFoundException|\InvalidArgumentException) {
			return new DataResponse(['error' => 'Preview unavailable'], Http::STATUS_NOT_FOUND);
		}
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function download(int $fileId): StreamResponse|DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		if ($fileId < 1) {
			return new DataResponse(['error' => 'Invalid file'], Http::STATUS_BAD_REQUEST);
		}

		$ownerUid = $this->resolveOwnerUid($fileId);
		if ($ownerUid === null) {
			return new DataResponse(['error' => 'File not found'], Http::STATUS_NOT_FOUND);
		}

		$file = $this->filesService->getFileForOwner($fileId, $ownerUid);
		if ($file === null) {
			return new DataResponse(['error' => 'File not found'], Http::STATUS_NOT_FOUND);
		}

		try {
			$handle = $file->fopen('r');
		} catch (\Throwable) {
			return new DataResponse(['error' => 'File not readable'], Http::STATUS_NOT_FOUND);
		}

		if ($handle === false) {
			return new DataResponse(['error' => 'File not readable'], Http::STATUS_NOT_FOUND);
		}

		$response = new StreamResponse($handle);
		$response->addHeader('Content-Type', $file->getMimeType());
		// inline so <video>/<img> can play/display; download UX uses the download attribute
		$response->addHeader(
			'Content-Disposition',
			'inline; filename="' . $this->escapeFilename($file->getName()) . '"',
		);
		$response->addHeader('Content-Length', (string)$file->getSize());

		return $response;
	}

	/**
	 * Owner uid for a Kinship-attached fileId, or null if not referenced.
	 */
	private function resolveOwnerUid(int $fileId): ?string {
		$media = $this->mediaMapper->findFirstByFileId($fileId);
		if ($media !== null) {
			return $media->getOwnerUid() ?? $media->getCreatedBy();
		}

		$person = $this->personMapper->findFirstByPhotoFileId($fileId);
		if ($person !== null) {
			return $person->getOwnerUid() ?? $person->getCreatedBy();
		}

		return null;
	}

	private function escapeFilename(string $name): string {
		return str_replace(['"', "\r", "\n"], '', $name);
	}
}
