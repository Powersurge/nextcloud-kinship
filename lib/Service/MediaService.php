<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Db\Media;
use OCA\Kinship\Db\MediaMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

/**
 * Media references linking Nextcloud file ids to genealogy objects. The full
 * attach/permission workflow is a v0.2 concern.
 */
class MediaService {
	public function __construct(
		private MediaMapper $mapper,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	public function get(int $id): Media {
		return $this->mapper->find($id);
	}

	/**
	 * @return Media[]
	 */
	public function getForSubject(string $subjectType, int $subjectId): array {
		return $this->mapper->findBySubject($subjectType, $subjectId);
	}

	public function create(array $data): Media {
		$uid = $this->userSession->getUser()?->getUID();

		$media = new Media();
		$media->setFileId((int)($data['fileId'] ?? 0));
		$media->setSubjectType($data['subjectType'] ?? '');
		$media->setSubjectId((int)($data['subjectId'] ?? 0));
		$media->setCaption($data['caption'] ?? null);
		$media->setIsPrimary((bool)($data['isPrimary'] ?? false));
		$media->setOwnerUid($uid);
		$media->setCreatedBy($uid);
		$media->setCreatedAt($this->timeFactory->getTime());

		return $this->mapper->insert($media);
	}

	public function update(int $id, array $data): Media {
		$media = $this->mapper->find($id);

		if (array_key_exists('caption', $data)) {
			$media->setCaption($data['caption']);
		}
		if (array_key_exists('isPrimary', $data)) {
			$media->setIsPrimary((bool)$data['isPrimary']);
		}

		return $this->mapper->update($media);
	}

	public function delete(int $id): void {
		$this->mapper->delete($this->mapper->find($id));
	}
}
