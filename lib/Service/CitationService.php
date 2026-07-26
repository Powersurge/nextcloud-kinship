<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Db\Citation;
use OCA\Kinship\Db\CitationMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

class CitationService {
	public function __construct(
		private CitationMapper $mapper,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	public function get(int $id): Citation {
		return $this->mapper->find($id);
	}

	/**
	 * @return Citation[]
	 */
	public function getForSource(int $sourceId): array {
		return $this->mapper->findBySource($sourceId);
	}

	/**
	 * @return Citation[]
	 */
	public function getForSubject(string $subjectType, int $subjectId): array {
		return $this->mapper->findBySubject($subjectType, $subjectId);
	}

	public function create(array $data): Citation {
		$now = $this->timeFactory->getTime();

		$citation = new Citation();
		$citation->setSourceId((int)($data['sourceId'] ?? 0));
		$citation->setSubjectType($data['subjectType'] ?? '');
		$citation->setSubjectId((int)($data['subjectId'] ?? 0));
		$citation->setDetail($data['detail'] ?? null);
		$citation->setTranscription($data['transcription'] ?? null);
		$citation->setConfidence($data['confidence'] ?? null);
		$citation->setAccessedDate($data['accessedDate'] ?? null);
		$citation->setCreatedBy($this->userSession->getUser()?->getUID());
		$citation->setCreatedAt($now);
		$citation->setUpdatedAt($now);

		return $this->mapper->insert($citation);
	}

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

		$citation->setUpdatedAt($this->timeFactory->getTime());

		return $this->mapper->update($citation);
	}

	public function delete(int $id): void {
		$this->mapper->delete($this->mapper->find($id));
	}
}
