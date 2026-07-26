<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\EventType;
use OCA\Kinship\Constants\LivingStatus;
use OCA\Kinship\Constants\ParticipantRole;
use OCA\Kinship\Db\DateValue;
use OCA\Kinship\Db\Event;
use OCA\Kinship\Db\EventMapper;
use OCA\Kinship\Db\Participant;
use OCA\Kinship\Db\ParticipantMapper;
use OCA\Kinship\Db\PersonMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

/**
 * Events CRUD. Persons are attached via participants; when a birth/death event
 * changes, the subject person's cached birth/death dates and living status are
 * refreshed so Person pages stay fast without duplicating the event data.
 */
class EventService {
	public function __construct(
		private EventMapper $mapper,
		private ParticipantMapper $participantMapper,
		private PersonMapper $personMapper,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	/**
	 * @return Event[]
	 */
	public function getAll(): array {
		return $this->mapper->findAll();
	}

	public function get(int $id): Event {
		return $this->mapper->find($id);
	}

	/**
	 * Events a person takes part in (via participants).
	 *
	 * @return Event[]
	 */
	public function getForPerson(int $personId): array {
		$eventIds = [];
		foreach ($this->participantMapper->findByPerson($personId) as $participant) {
			$eventIds[$participant->getEventId()] = true;
		}

		$events = [];
		foreach (array_keys($eventIds) as $eventId) {
			$event = $this->mapper->findOrNull($eventId);
			if ($event !== null) {
				$events[] = $event;
			}
		}

		return $events;
	}

	/**
	 * @return Event[]
	 */
	public function getForFamily(int $familyId): array {
		return $this->mapper->findByFamily($familyId);
	}

	/**
	 * @return Participant[]
	 */
	public function getParticipants(int $eventId): array {
		return $this->participantMapper->findByEvent($eventId);
	}

	public function create(array $data): Event {
		$now = $this->timeFactory->getTime();
		$uid = $this->userSession->getUser()?->getUID();

		$type = $data['type'] ?? EventType::CUSTOM;
		if (!EventType::isValid($type)) {
			$type = EventType::CUSTOM;
		}

		$event = new Event();
		$event->setType($type);
		$event->setCustomEventId(
			$type === EventType::CUSTOM && isset($data['customEventId'])
				? (int)$data['customEventId']
				: null
		);
		$event->setTitle($data['title'] ?? null);
		$event->setDescription($data['description'] ?? null);
		$event->setPlaceId(isset($data['placeId']) ? (int)$data['placeId'] : null);
		$event->setDate(DateValue::fromArray($data['date'] ?? null));
		$event->setFamilyId(isset($data['familyId']) ? (int)$data['familyId'] : null);
		$event->setOwnerUid($uid);
		$event->setCreatedBy($uid);
		$event->setCreatedAt($now);
		$event->setUpdatedAt($now);

		$event = $this->mapper->insert($event);

		if (isset($data['participants']) && is_array($data['participants'])) {
			foreach ($data['participants'] as $participantData) {
				$this->addParticipant($event->getId(), $participantData, $now, $uid);
			}
		}

		$this->syncAffectedPersons($event);

		return $event;
	}

	public function updateEvent(int $id, array $data): Event {
		$event = $this->mapper->find($id);

		if (array_key_exists('title', $data)) {
			$event->setTitle($data['title']);
		}
		if (array_key_exists('description', $data)) {
			$event->setDescription($data['description']);
		}
		if (array_key_exists('type', $data) && EventType::isValid($data['type'])) {
			$event->setType($data['type']);
		}
		if (array_key_exists('customEventId', $data)) {
			$event->setCustomEventId($data['customEventId'] !== null ? (int)$data['customEventId'] : null);
		}
		if (array_key_exists('placeId', $data)) {
			$event->setPlaceId($data['placeId'] !== null ? (int)$data['placeId'] : null);
		}
		if (array_key_exists('date', $data)) {
			$event->setDate(DateValue::fromArray($data['date']));
		}
		if (array_key_exists('familyId', $data)) {
			$event->setFamilyId($data['familyId'] !== null ? (int)$data['familyId'] : null);
		}

		$event->setUpdatedAt($this->timeFactory->getTime());
		$event = $this->mapper->update($event);

		$this->syncAffectedPersons($event);

		return $event;
	}

	public function deleteEvent(int $id): void {
		$event = $this->mapper->find($id);

		$affected = [];
		foreach ($this->participantMapper->findByEvent($id) as $participant) {
			if ($participant->getRole() === ParticipantRole::SUBJECT) {
				$affected[$participant->getPersonId()] = true;
			}
			$this->participantMapper->delete($participant);
		}

		$this->mapper->delete($event);

		if (in_array($event->getType(), [EventType::BIRTH, EventType::DEATH], true)) {
			foreach (array_keys($affected) as $personId) {
				$this->syncPersonLifeDates($personId);
			}
		}
	}

	private function addParticipant(int $eventId, array $data, int $now, ?string $uid): void {
		$participant = new Participant();
		$participant->setEventId($eventId);
		$participant->setPersonId((int)($data['personId'] ?? 0));
		$participant->setRole($data['role'] ?? ParticipantRole::SUBJECT);
		$participant->setNote($data['note'] ?? null);
		$participant->setCreatedBy($uid);
		$participant->setCreatedAt($now);
		$this->participantMapper->insert($participant);
	}

	private function syncAffectedPersons(Event $event): void {
		if (!in_array($event->getType(), [EventType::BIRTH, EventType::DEATH], true)) {
			return;
		}

		foreach ($this->participantMapper->findByEvent($event->getId()) as $participant) {
			if ($participant->getRole() === ParticipantRole::SUBJECT) {
				$this->syncPersonLifeDates($participant->getPersonId());
			}
		}
	}

	/**
	 * Recompute a person's cached birth/death dates and living status from
	 * their birth/death events (the source of truth).
	 */
	public function syncPersonLifeDates(int $personId): void {
		$person = $this->personMapper->findOrNull($personId);
		if ($person === null) {
			return;
		}

		$birth = null;
		$death = null;
		$hasDeathEvent = false;

		foreach ($this->participantMapper->findByPerson($personId) as $participant) {
			if ($participant->getRole() !== ParticipantRole::SUBJECT) {
				continue;
			}
			$event = $this->mapper->findOrNull($participant->getEventId());
			if ($event === null) {
				continue;
			}
			if ($event->getType() === EventType::BIRTH) {
				$birth = $event->getDate();
			} elseif ($event->getType() === EventType::DEATH) {
				$death = $event->getDate();
				$hasDeathEvent = true;
			}
		}

		$person->setBirthDateStart($birth?->getStartIso());
		$person->setBirthDateDisplay($birth?->getDisplayText());
		$person->setDeathDateStart($death?->getStartIso());
		$person->setDeathDateDisplay($death?->getDisplayText());

		// A death event means deceased regardless of whether its date is known.
		if ($hasDeathEvent) {
			$person->setLivingStatus(LivingStatus::DECEASED);
		}

		$person->setUpdatedAt($this->timeFactory->getTime());
		$this->personMapper->update($person);
	}
}
