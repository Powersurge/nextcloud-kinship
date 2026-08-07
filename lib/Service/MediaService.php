<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\SubjectType;
use OCA\Kinship\Db\AlbumMapper;
use OCA\Kinship\Db\EventMapper;
use OCA\Kinship\Db\FamilyMapper;
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
		private FamilyMapper $familyMapper,
		private EventMapper $eventMapper,
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
			$this->clearCoverPhotoIfMatches($media);
		}

		return $this->hydrateFiles([$this->mapper->find($media->getId())])[0];
	}

	public function delete(int $id): void {
		$media = $this->mapper->find($id);
		$this->mapper->clearCoverReferences($id);
		if ($media->getIsPrimary()) {
			$this->clearCoverPhotoIfMatches($media);
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

		$this->setCoverPhoto($media->getSubjectType(), $media->getSubjectId(), $media->getFileId());
	}

	private function clearCoverPhotoIfMatches(Media $media): void {
		$type = $media->getSubjectType();
		$id = $media->getSubjectId();
		$fileId = $media->getFileId();

		if ($type === SubjectType::PERSON) {
			$person = $this->personMapper->find($id);
			if ($person->getPhotoFileId() === $fileId) {
				$person->setPhotoFileId(null);
				$person->setUpdatedAt($this->timeFactory->getTime());
				$this->personMapper->update($person);
			}
			return;
		}

		if ($type === SubjectType::FAMILY) {
			$family = $this->familyMapper->find($id);
			if ($family->getPhotoFileId() === $fileId) {
				$family->setPhotoFileId(null);
				$family->setUpdatedAt($this->timeFactory->getTime());
				$this->familyMapper->update($family);
			}
			return;
		}

		if ($type === SubjectType::EVENT) {
			$event = $this->eventMapper->find($id);
			if ($event->getPhotoFileId() === $fileId) {
				$event->setPhotoFileId(null);
				$event->setUpdatedAt($this->timeFactory->getTime());
				$this->eventMapper->update($event);
			}
		}
	}

	private function setCoverPhoto(string $subjectType, int $subjectId, int $fileId): void {
		if ($subjectType === SubjectType::PERSON) {
			$person = $this->personMapper->find($subjectId);
			$person->setPhotoFileId($fileId);
			$person->setUpdatedAt($this->timeFactory->getTime());
			$this->personMapper->update($person);
			return;
		}

		if ($subjectType === SubjectType::FAMILY) {
			$family = $this->familyMapper->find($subjectId);
			$family->setPhotoFileId($fileId);
			$family->setUpdatedAt($this->timeFactory->getTime());
			$this->familyMapper->update($family);
			return;
		}

		if ($subjectType === SubjectType::EVENT) {
			$event = $this->eventMapper->find($subjectId);
			$event->setPhotoFileId($fileId);
			$event->setUpdatedAt($this->timeFactory->getTime());
			$this->eventMapper->update($event);
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
