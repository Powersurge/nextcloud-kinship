<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Db\DateValue;
use OCA\Kinship\Db\Fact;
use OCA\Kinship\Db\FactMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

class FactService {
	public function __construct(
		private FactMapper $mapper,
		private PlaceService $placeService,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	public function get(int $id): Fact {
		return $this->hydratePlaces([$this->mapper->find($id)])[0];
	}

	/**
	 * @return Fact[]
	 */
	public function getForSubject(string $subjectType, int $subjectId): array {
		return $this->hydratePlaces($this->mapper->findBySubject($subjectType, $subjectId));
	}

	public function create(array $data): Fact {
		$now = $this->timeFactory->getTime();
		$uid = $this->userSession->getUser()?->getUID();

		$fact = new Fact();
		$fact->setSubjectType($data['subjectType'] ?? '');
		$fact->setSubjectId((int)($data['subjectId'] ?? 0));
		$fact->setType($data['type'] ?? '');
		$fact->setValue($data['value'] ?? null);
		$fact->setPlaceId(isset($data['placeId']) ? (int)$data['placeId'] : null);
		$fact->setDate(DateValue::fromArray($data['date'] ?? null));
		$fact->setNote($data['note'] ?? null);
		$fact->setOwnerUid($uid);
		$fact->setCreatedBy($uid);
		$fact->setCreatedAt($now);
		$fact->setUpdatedAt($now);

		return $this->hydratePlaces([$this->mapper->insert($fact)])[0];
	}

	public function update(int $id, array $data): Fact {
		$fact = $this->mapper->find($id);

		if (array_key_exists('type', $data)) {
			$fact->setType($data['type']);
		}
		if (array_key_exists('value', $data)) {
			$fact->setValue($data['value']);
		}
		if (array_key_exists('placeId', $data)) {
			$fact->setPlaceId($data['placeId'] !== null ? (int)$data['placeId'] : null);
		}
		if (array_key_exists('date', $data)) {
			$fact->setDate(DateValue::fromArray($data['date']));
		}
		if (array_key_exists('note', $data)) {
			$fact->setNote($data['note']);
		}

		$fact->setUpdatedAt($this->timeFactory->getTime());

		return $this->hydratePlaces([$this->mapper->update($fact)])[0];
	}

	public function delete(int $id): void {
		$this->mapper->delete($this->mapper->find($id));
	}

	/**
	 * @param Fact[] $facts
	 * @return Fact[]
	 */
	private function hydratePlaces(array $facts): array {
		$summaries = $this->placeService->summarizeByIds(
			array_map(static fn (Fact $fact) => $fact->getPlaceId(), $facts)
		);

		foreach ($facts as $fact) {
			$placeId = $fact->getPlaceId();
			$fact->setPlace($placeId !== null ? ($summaries[$placeId] ?? null) : null);
		}

		return $facts;
	}
}
