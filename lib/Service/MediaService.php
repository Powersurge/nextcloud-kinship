<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\SubjectType;
use OCA\Kinship\Db\AlbumMapper;
use OCA\Kinship\Db\Media;
use OCA\Kinship\Db\MediaMapper;
use OCA\Kinship\Db\PersonMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;
use RuntimeException;

/**
 * Media references linking Nextcloud file ids to genealogy objects.
 */
class MediaService {
	public function __construct(
		private MediaMapper $mapper,
		private AlbumMapper $albumMapper,
		private PersonMapper $personMapper,
		private FilesService $filesService,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	public function get(int $id): Media {
		return $this->hydrateFiles([$this->mapper->find($id)])[0];
	}

	/**
	 * @return Media[]
	 */
	public function getForSubject(string $subjectType, int $subjectId, ?int $albumId = null): array {
		return $this->hydrateFiles($this->mapper->findBySubject($subjectType, $subjectId, $albumId));
	}

	/**
	 * @return Media[]
	 */
	public function getForAlbum(int $albumId): array {
		return $this->hydrateFiles($this->mapper->findByAlbum($albumId));
	}

	/**
	 * @param array{
	 *   fileId: int,
	 *   subjectType: string,
	 *   subjectId: int,
	 *   albumId?: ?int,
	 *   caption?: ?string,
	 *   isPrimary?: bool,
	 *   sortOrder?: int
	 * } $data
	 */
	public function create(array $data): Media {
		$subjectType = (string)($data['subjectType'] ?? '');
		if (!SubjectType::isValid($subjectType)) {
			throw new RuntimeException('Invalid subject type');
		}

		$fileId = (int)$data['fileId'];
		$this->filesService->assertUserCanAccess($fileId);

		$albumId = array_key_exists('albumId', $data) && $data['albumId'] !== null
			? (int)$data['albumId']
			: null;
		$subjectId = (int)$data['subjectId'];
		$this->assertAlbumMatchesSubject($albumId, $subjectType, $subjectId);

		$uid = $this->userSession->getUser()?->getUID();
		$isPrimary = (bool)($data['isPrimary'] ?? false);

		$media = new Media();
		$media->setFileId($fileId);
		$media->setSubjectType($subjectType);
		$media->setSubjectId($subjectId);
		$media->setAlbumId($albumId);
		$media->setSortOrder((int)($data['sortOrder'] ?? 0));
		$media->setCaption($data['caption'] ?? null);
		$media->setIsPrimary($isPrimary);
		$media->setOwnerUid($uid);
		$media->setCreatedBy($uid);
		$media->setCreatedAt($this->timeFactory->getTime());

		$media = $this->mapper->insert($media);

		if ($isPrimary) {
			$this->applyPrimary($media);
		}

		return $this->hydrateFiles([$this->mapper->find($media->getId())])[0];
	}

	/**
	 * @param array<string, mixed> $data
	 */
	public function update(int $id, array $data): Media {
		$media = $this->mapper->find($id);

		if (array_key_exists('caption', $data)) {
			$media->setCaption($data['caption']);
		}
		if (array_key_exists('sortOrder', $data)) {
			$media->setSortOrder((int)$data['sortOrder']);
		}
		if (array_key_exists('albumId', $data)) {
			$albumId = $data['albumId'] !== null ? (int)$data['albumId'] : null;
			$this->assertAlbumMatchesSubject($albumId, $media->getSubjectType(), $media->getSubjectId());
			$media->setAlbumId($albumId);
		}

		$becamePrimary = false;
		$clearedPrimary = false;
		if (array_key_exists('isPrimary', $data)) {
			$next = (bool)$data['isPrimary'];
			if ($next && !$media->getIsPrimary()) {
				$becamePrimary = true;
			}
			if (!$next && $media->getIsPrimary()) {
				$clearedPrimary = true;
			}
			$media->setIsPrimary($next);
		}

		$media = $this->mapper->update($media);

		if ($becamePrimary) {
			$this->applyPrimary($media);
		} elseif ($clearedPrimary) {
			$this->clearPersonPhotoIfMatches($media);
		}

		return $this->hydrateFiles([$this->mapper->find($media->getId())])[0];
	}

	public function delete(int $id): void {
		$media = $this->mapper->find($id);
		$this->mapper->clearCoverReferences($id);
		if ($media->getIsPrimary()) {
			$this->clearPersonPhotoIfMatches($media);
		}
		$this->mapper->delete($media);
	}

	public function deleteBySubject(string $subjectType, int $subjectId): void {
		$this->mapper->deleteBySubject($subjectType, $subjectId);
	}

	public function deleteByAlbum(int $albumId): void {
		$this->mapper->deleteByAlbum($albumId);
	}

	private function assertAlbumMatchesSubject(?int $albumId, string $subjectType, int $subjectId): void {
		if ($albumId === null) {
			return;
		}

		$album = $this->albumMapper->findOrNull($albumId);
		if ($album === null) {
			throw new RuntimeException('Album not found');
		}
		if ($album->getOwnerType() !== $subjectType || $album->getOwnerId() !== $subjectId) {
			throw new RuntimeException('Album does not belong to this subject');
		}
	}

	private function applyPrimary(Media $media): void {
		$this->mapper->clearPrimaryForSubject(
			$media->getSubjectType(),
			$media->getSubjectId(),
			$media->getId(),
		);

		if ($media->getSubjectType() === SubjectType::PERSON) {
			$person = $this->personMapper->find($media->getSubjectId());
			$person->setPhotoFileId($media->getFileId());
			$person->setUpdatedAt($this->timeFactory->getTime());
			$this->personMapper->update($person);
		}
	}

	private function clearPersonPhotoIfMatches(Media $media): void {
		if ($media->getSubjectType() !== SubjectType::PERSON) {
			return;
		}

		$person = $this->personMapper->find($media->getSubjectId());
		if ($person->getPhotoFileId() === $media->getFileId()) {
			$person->setPhotoFileId(null);
			$person->setUpdatedAt($this->timeFactory->getTime());
			$this->personMapper->update($person);
		}
	}

	/**
	 * @param Media[] $items
	 * @return Media[]
	 */
	private function hydrateFiles(array $items): array {
		foreach ($items as $media) {
			$ownerUid = $media->getOwnerUid() ?? $media->getCreatedBy();
			$media->setFile(
				$this->filesService->resolveAttachedFile($media->getFileId(), $ownerUid),
			);
		}

		return $items;
	}
}
