<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Db\Citation;
use OCA\Kinship\Db\CitationMapper;
use OCA\Kinship\Db\Source;
use OCA\Kinship\Db\SourceMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

/**
 * Citations persistence. Callers must pass already-validated data
 * (see {@see \OCA\Kinship\Http\Requests\CitationRequest}).
 */
class CitationService {
	public function __construct(
		private CitationMapper $mapper,
		private SourceMapper $sourceMapper,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	public function get(int $id): Citation {
		return $this->hydrateSources([$this->mapper->find($id)])[0];
	}

	/**
	 * @return Citation[]
	 */
	public function getForSource(int $sourceId): array {
		return $this->hydrateSources($this->mapper->findBySource($sourceId));
	}

	/**
	 * @return Citation[]
	 */
	public function getForSubject(string $subjectType, int $subjectId): array {
		return $this->hydrateSources($this->mapper->findBySubject($subjectType, $subjectId));
	}

	/**
	 * @param array{
	 *   sourceId: int,
	 *   subjectType: string,
	 *   subjectId: int,
	 *   detail?: ?string,
	 *   transcription?: ?string,
	 *   confidence?: ?string,
	 *   accessedDate?: ?string
	 * } $data
	 */
	public function create(array $data): Citation {
		$now = $this->timeFactory->getTime();

		$citation = new Citation();
		$citation->setSourceId((int)$data['sourceId']);
		$citation->setSubjectType((string)$data['subjectType']);
		$citation->setSubjectId((int)$data['subjectId']);
		$citation->setDetail($data['detail'] ?? null);
		$citation->setTranscription($data['transcription'] ?? null);
		$citation->setConfidence($data['confidence'] ?? null);
		$citation->setAccessedDate($data['accessedDate'] ?? null);
		$citation->setCreatedBy($this->userSession->getUser()?->getUID());
		$citation->setCreatedAt($now);
		$citation->setUpdatedAt($now);

		return $this->hydrateSources([$this->mapper->insert($citation)])[0];
	}

	/**
	 * @param array<string, mixed> $data validated partial payload
	 */
	public function update(int $id, array $data): Citation {
		$citation = $this->mapper->find($id);

		foreach ([
			'detail' => 'setDetail',
			'transcription' => 'setTranscription',
			'confidence' => 'setConfidence',
			'accessedDate' => 'setAccessedDate',
		] as $key => $setter) {
			if (array_key_exists($key, $data)) {
				$citation->$setter($data[$key]);
			}
		}

		if (array_key_exists('sourceId', $data)) {
			$citation->setSourceId((int)$data['sourceId']);
		}

		$citation->setUpdatedAt($this->timeFactory->getTime());

		return $this->hydrateSources([$this->mapper->update($citation)])[0];
	}

	public function delete(int $id): void {
		$this->mapper->delete($this->mapper->find($id));
	}

	public function deleteBySource(int $sourceId): void {
		$this->mapper->deleteBySource($sourceId);
	}

	public function deleteBySubject(string $subjectType, int $subjectId): void {
		$this->mapper->deleteBySubject($subjectType, $subjectId);
	}

	public function countBySource(int $sourceId): int {
		return $this->mapper->countBySource($sourceId);
	}

	/**
	 * @param Citation[] $citations
	 * @return Citation[]
	 */
	private function hydrateSources(array $citations): array {
		$ids = [];
		foreach ($citations as $citation) {
			$ids[$citation->getSourceId()] = true;
		}

		/** @var array<int, Source> $sources */
		$sources = [];
		foreach (array_keys($ids) as $sourceId) {
			$source = $this->sourceMapper->findOrNull((int)$sourceId);
			if ($source !== null) {
				$sources[$source->getId()] = $source;
			}
		}

		foreach ($citations as $citation) {
			$source = $sources[$citation->getSourceId()] ?? null;
			$citation->setSource($source !== null ? [
				'id' => $source->getId(),
				'title' => $source->getTitle(),
				'sourceType' => $source->getSourceType(),
				'author' => $source->getAuthor(),
			] : null);
		}

		return $citations;
	}
}
