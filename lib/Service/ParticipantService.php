<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\ParticipantRole;
use OCA\Kinship\Db\Participant;
use OCA\Kinship\Db\ParticipantMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

class ParticipantService {
	public function __construct(
		private ParticipantMapper $mapper,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	/**
	 * @return Participant[]
	 */
	public function getForEvent(int $eventId): array {
		return $this->mapper->findByEvent($eventId);
	}

	/**
	 * @return Participant[]
	 */
	public function getForPerson(int $personId): array {
		return $this->mapper->findByPerson($personId);
	}

	public function create(array $data): Participant {
		$participant = new Participant();
		$participant->setEventId((int)($data['eventId'] ?? 0));
		$participant->setPersonId((int)($data['personId'] ?? 0));
		$participant->setRole($data['role'] ?? ParticipantRole::SUBJECT);
		$participant->setNote($data['note'] ?? null);
		$participant->setCreatedBy($this->userSession->getUser()?->getUID());
		$participant->setCreatedAt($this->timeFactory->getTime());

		return $this->mapper->insert($participant);
	}

	public function delete(int $id): void {
		$this->mapper->delete($this->mapper->find($id));
	}
}
