<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IPreview;
use OCP\IUserSession;
use RuntimeException;

/**
 * Nextcloud Files helpers for Kinship media (inbox uploads + file resolution).
 */
class FilesService {
	private const KINSHIP_FOLDER = 'Kinship';
	private const UPLOADS_FOLDER = 'Uploads';

	public function __construct(
		private IRootFolder $rootFolder,
		private IUserSession $userSession,
		private IPreview $previewManager,
	) {
	}

	/**
	 * Ensure Kinship/Uploads exists under the current user's files.
	 *
	 * @throws RuntimeException
	 */
	public function ensureUploadsFolder(): Folder {
		$userFolder = $this->userFolder();

		try {
			$kinship = $userFolder->get(self::KINSHIP_FOLDER);
			if (!($kinship instanceof Folder)) {
				throw new RuntimeException('Kinship path exists but is not a folder');
			}
		} catch (NotFoundException) {
			$kinship = $userFolder->newFolder(self::KINSHIP_FOLDER);
		}

		try {
			$uploads = $kinship->get(self::UPLOADS_FOLDER);
			if (!($uploads instanceof Folder)) {
				throw new RuntimeException('Kinship/Uploads path exists but is not a folder');
			}
		} catch (NotFoundException) {
			$uploads = $kinship->newFolder(self::UPLOADS_FOLDER);
		}

		return $uploads;
	}

	/**
	 * Store a new file in Kinship/Uploads and return its file id.
	 *
	 * @param resource|string $content
	 * @throws RuntimeException
	 */
	public function uploadToInbox(string $name, mixed $content): int {
		$uploads = $this->ensureUploadsFolder();
		$safeName = $this->uniqueName($uploads, $this->sanitizeFilename($name));

		try {
			$file = $uploads->newFile($safeName, $content);
		} catch (NotPermittedException $e) {
			throw new RuntimeException('Cannot write to Kinship/Uploads: ' . $e->getMessage(), 0, $e);
		}

		return $file->getId();
	}

	/**
	 * Resolve a file the current user can access.
	 *
	 * path is relative to the user folder (e.g. /Kinship/Uploads/photo.jpg)
	 * so it can be used with WebDAV / download helpers.
	 *
	 * @return array{fileId: int, name: string, path: string, mime: string, hasPreview: bool, size: int, mtime: int}|null
	 */
	public function resolveFile(int $fileId): ?array {
		$uid = $this->userSession->getUser()?->getUID();
		if ($uid === null) {
			return null;
		}

		$file = $this->getAccessibleFile($fileId);
		if ($file === null) {
			return null;
		}

		$userFolder = $this->rootFolder->getUserFolder($uid);
		$relativePath = $userFolder->getRelativePath($file->getPath());
		if ($relativePath === null || $relativePath === '') {
			return null;
		}

		if (!str_starts_with($relativePath, '/')) {
			$relativePath = '/' . $relativePath;
		}

		return $this->summarizeFile($file, $relativePath);
	}

	/**
	 * Resolve a file from an owner's storage (for Kinship proxy hydration).
	 * path is empty — the current user may not have DAV access.
	 *
	 * @return array{fileId: int, name: string, path: string, mime: string, hasPreview: bool, size: int, mtime: int}|null
	 */
	public function resolveFileForOwner(int $fileId, string $ownerUid): ?array {
		$file = $this->getFileForOwner($fileId, $ownerUid);
		if ($file === null) {
			return null;
		}

		return $this->summarizeFile($file, '');
	}

	/**
	 * Prefer current-user access (with DAV path); fall back to owner metadata.
	 *
	 * @return array{fileId: int, name: string, path: string, mime: string, hasPreview: bool, size: int, mtime: int}|null
	 */
	public function resolveAttachedFile(int $fileId, ?string $ownerUid): ?array {
		$resolved = $this->resolveFile($fileId);
		if ($resolved !== null) {
			return $resolved;
		}

		if ($ownerUid === null || $ownerUid === '') {
			return null;
		}

		return $this->resolveFileForOwner($fileId, $ownerUid);
	}

	public function getFileForOwner(int $fileId, string $ownerUid): ?File {
		if ($ownerUid === '') {
			return null;
		}

		try {
			$nodes = $this->rootFolder->getUserFolder($ownerUid)->getById($fileId);
		} catch (\Exception) {
			return null;
		}

		foreach ($nodes as $node) {
			if ($node instanceof File) {
				return $node;
			}
		}

		return null;
	}

	/**
	 * @throws RuntimeException when the file is missing or inaccessible
	 */
	public function assertUserCanAccess(int $fileId): void {
		if ($this->getAccessibleFile($fileId) === null) {
			throw new RuntimeException('File not found or inaccessible');
		}
	}

	/**
	 * @return array{fileId: int, name: string, path: string, mime: string, hasPreview: bool, size: int, mtime: int}
	 */
	private function summarizeFile(File $file, string $path): array {
		return [
			'fileId' => $file->getId(),
			'name' => $file->getName(),
			'path' => $path,
			'mime' => $file->getMimeType(),
			'hasPreview' => $this->previewManager->isAvailable($file),
			'size' => $file->getSize(),
			'mtime' => $file->getMTime(),
		];
	}

	private function getAccessibleFile(int $fileId): ?File {
		$uid = $this->userSession->getUser()?->getUID();
		if ($uid === null) {
			return null;
		}

		try {
			$nodes = $this->rootFolder->getUserFolder($uid)->getById($fileId);
		} catch (\Exception) {
			return null;
		}

		foreach ($nodes as $node) {
			if ($node instanceof File) {
				return $node;
			}
		}

		return null;
	}

	private function userFolder(): Folder {
		$uid = $this->userSession->getUser()?->getUID();
		if ($uid === null) {
			throw new RuntimeException('No authenticated user');
		}

		return $this->rootFolder->getUserFolder($uid);
	}

	private function sanitizeFilename(string $name): string {
		$name = basename(str_replace(["\0", '/', '\\'], '', $name));
		$name = trim($name);
		if ($name === '' || $name === '.' || $name === '..') {
			return 'upload.bin';
		}

		return $name;
	}

	private function uniqueName(Folder $folder, string $name): string {
		if (!$folder->nodeExists($name)) {
			return $name;
		}

		$pathInfo = pathinfo($name);
		$base = $pathInfo['filename'] ?? 'upload';
		$ext = isset($pathInfo['extension']) && $pathInfo['extension'] !== ''
			? '.' . $pathInfo['extension']
			: '';

		$i = 1;
		do {
			$candidate = $base . ' (' . $i . ')' . $ext;
			$i++;
		} while ($folder->nodeExists($candidate));

		return $candidate;
	}
}
