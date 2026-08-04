<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\SubjectType;
use OCA\Kinship\Db\Album;
use OCA\Kinship\Db\AlbumMapper;
use OCA\Kinship\Db\MediaMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;
use RuntimeException;

class AlbumService {
	public function __construct(
		private AlbumMapper $mapper,
		private MediaMapper $mediaMapper,
		private FilesService $filesService,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	public function get(int $id): Album {
		return $this->hydrateCovers([$this->mapper->find($id)])[0];
	}

	/**
	 * @return Album[]
	 */
	public function getForOwner(string $ownerType, int $ownerId): array {
		return $this->hydrateCovers($this->mapper->findByOwner($ownerType, $ownerId));
	}

	/**
	 * @param array{
	 *   title: string,
	 *   description?: ?string,
	 *   ownerType: string,
	 *   ownerId: int,
	 *   coverMediaId?: ?int
	 * } $data
	 */
	public function create(array $data): Album {
		if (!SubjectType::isAlbumOwner((string)$data['ownerType'])) {
			throw new RuntimeException('Invalid album owner type');
		}

		$now = $this->timeFactory->getTime();
		$uid = $this->userSession->getUser()?->getUID();

		$album = new Album();
		$album->setTitle((string)$data['title']);
		$album->setDescription($data['description'] ?? null);
		$album->setOwnerType((string)$data['ownerType']);
		$album->setOwnerId((int)$data['ownerId']);
		$album->setCoverMediaId(isset($data['coverMediaId']) ? (int)$data['coverMediaId'] : null);
		$album->setOwnerUid($uid);
		$album->setCreatedBy($uid);
		$album->setCreatedAt($now);
		$album->setUpdatedAt($now);

		if ($album->getCoverMediaId() !== null) {
			$this->assertCoverBelongsToAlbumOwner($album, $album->getCoverMediaId());
		}

		return $this->hydrateCovers([$this->mapper->insert($album)])[0];
	}

	/**
	 * @param array<string, mixed> $data
	 */
	public function update(int $id, array $data): Album {
		$album = $this->mapper->find($id);

		if (array_key_exists('title', $data)) {
			$album->setTitle((string)$data['title']);
		}
		if (array_key_exists('description', $data)) {
			$album->setDescription($data['description']);
		}
		if (array_key_exists('coverMediaId', $data)) {
			$coverId = $data['coverMediaId'] !== null ? (int)$data['coverMediaId'] : null;
			if ($coverId !== null) {
				$this->assertCoverBelongsToAlbum($album, $coverId);
			}
			$album->setCoverMediaId($coverId);
		}

		$album->setUpdatedAt($this->timeFactory->getTime());

		return $this->hydrateCovers([$this->mapper->update($album)])[0];
	}

	public function delete(int $id): void {
		$album = $this->mapper->find($id);
		$this->mediaMapper->deleteByAlbum($id);
		$this->mapper->delete($album);
	}

	public function deleteByOwner(string $ownerType, int $ownerId): void {
		$albums = $this->mapper->findByOwner($ownerType, $ownerId);
		foreach ($albums as $album) {
			$this->mediaMapper->deleteByAlbum($album->getId());
		}
		$this->mapper->deleteByOwner($ownerType, $ownerId);
	}

	private function assertCoverBelongsToAlbum(Album $album, int $mediaId): void {
		$media = $this->mediaMapper->findOrNull($mediaId);
		if ($media === null
			|| $media->getAlbumId() !== $album->getId()
			|| $media->getSubjectType() !== $album->getOwnerType()
			|| $media->getSubjectId() !== $album->getOwnerId()
		) {
			throw new RuntimeException('Cover media must belong to this album');
		}
	}

	private function assertCoverBelongsToAlbumOwner(Album $album, int $mediaId): void {
		$media = $this->mediaMapper->findOrNull($mediaId);
		if ($media === null
			|| $media->getSubjectType() !== $album->getOwnerType()
			|| $media->getSubjectId() !== $album->getOwnerId()
		) {
			throw new RuntimeException('Cover media must belong to this album owner');
		}
	}

	/**
	 * @param Album[] $albums
	 * @return Album[]
	 */
	private function hydrateCovers(array $albums): array {
		foreach ($albums as $album) {
			$coverId = $album->getCoverMediaId();
			if ($coverId === null) {
				$album->setCoverFile(null);
				continue;
			}

			$media = $this->mediaMapper->findOrNull($coverId);
			if ($media === null) {
				$album->setCoverFile(null);
				continue;
			}

			$album->setCoverFile($this->filesService->resolveFile($media->getFileId()));
		}

		return $albums;
	}
}
