<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\SubjectType;
use OCA\Kinship\Db\Source;
use OCA\Kinship\Db\SourceMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

/**
 * Sources persistence. Callers must pass already-validated data
 * (see {@see \OCA\Kinship\Http\Requests\SourceRequest}).
 */
class SourceService {
	public function __construct(
		private SourceMapper $mapper,
		private CitationService $citationService,
		private MediaService $mediaService,
		private AlbumService $albumService,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	/**
	 * @return Source[]
	 */
	public function getAll(): array {
		return $this->mapper->findAll();
	}

	public function get(int $id): Source {
		return $this->mapper->find($id);
	}

	/**
	 * @param array{
	 *   title: string,
	 *   author?: ?string,
	 *   publication?: ?string,
	 *   repository?: ?string,
	 *   sourceType?: ?string,
	 *   url?: ?string,
	 *   notes?: ?string
	 * } $data
	 */
	public function create(array $data): Source {
		$now = $this->timeFactory->getTime();
		$uid = $this->userSession->getUser()?->getUID();

		$source = new Source();
		$source->setTitle($data['title']);
		$source->setAuthor($data['author'] ?? null);
		$source->setPublication($data['publication'] ?? null);
		$source->setRepository($data['repository'] ?? null);
		$source->setSourceType($data['sourceType'] ?? null);
		$source->setUrl($data['url'] ?? null);
		$source->setNotes($data['notes'] ?? null);
		$source->setOwnerUid($uid);
		$source->setCreatedBy($uid);
		$source->setCreatedAt($now);
		$source->setUpdatedAt($now);

		return $this->mapper->insert($source);
	}

	/**
	 * @param array<string, mixed> $data validated partial payload
	 */
	public function update(int $id, array $data): Source {
		$source = $this->mapper->find($id);

		foreach ([
			'title' => 'setTitle',
			'author' => 'setAuthor',
			'publication' => 'setPublication',
			'repository' => 'setRepository',
			'sourceType' => 'setSourceType',
			'url' => 'setUrl',
			'notes' => 'setNotes',
		] as $key => $setter) {
			if (array_key_exists($key, $data)) {
				$source->$setter($data[$key]);
			}
		}

		$source->setUpdatedAt($this->timeFactory->getTime());

		return $this->mapper->update($source);
	}

	public function delete(int $id): void {
		$source = $this->mapper->find($id);
		$this->citationService->deleteBySource($id);
		$this->albumService->deleteByOwner(SubjectType::SOURCE, $id);
		$this->mediaService->deleteBySubject(SubjectType::SOURCE, $id);
		$this->mapper->delete($source);
	}
}
