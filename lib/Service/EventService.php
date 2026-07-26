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
 * Events CRUD. Persons are attached via participants. Birth and death dates
 * live only here: nothing is copied onto the person, only living_status is
 * derived when a death event exists.
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

		if ($event->getType() === EventType::DEATH) {
			foreach (array_keys($affected) as $personId) {
				$this->syncPersonLivingStatus($personId);
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
		if ($event->getType() !== EventType::DEATH) {
			return;
		}

		foreach ($this->participantMapper->findByEvent($event->getId()) as $participant) {
			if ($participant->getRole() === ParticipantRole::SUBJECT) {
				$this->syncPersonLivingStatus($participant->getPersonId());
			}
		}
	}

	/**
	 * Set a person's birth or death date by writing it to their birth/death
	 * event, creating that event (with the person as its subject) when they
	 * do not have one yet. An empty date on a person with no such event is a
	 * no-op, so that saving a person form without dates does not create empty
	 * birth/death events.
	 *
	 * @param array<string, mixed>|null $date DateValue payload
	 */
	public function setLifeDate(int $personId, string $type, ?array $date): void {
		if (!in_array($type, [EventType::BIRTH, EventType::DEATH], true)) {
			return;
		}

		$event = $this->mapper->findLifeEvent($personId, $type);

		if ($event !== null) {
			$this->updateEvent($event->getId(), ['date' => $date]);

			return;
		}

		if (DateValue::fromArray($date)->isEmpty()) {
			return;
		}

		$this->create([
			'type' => $type,
			'date' => $date,
			'participants' => [
				[
					'personId' => $personId,
					'role' => ParticipantRole::SUBJECT,
				],
			],
		]);
	}

	/**
	 * A death event means the person is deceased, regardless of whether its
	 * date is known. Dates themselves stay on the events.
	 */
	public function syncPersonLivingStatus(int $personId): void {
		$person = $this->personMapper->findOrNull($personId);
		if ($person === null) {
			return;
		}

		if ($this->mapper->findLifeEvent($personId, EventType::DEATH) === null) {
			return;
		}

		if ($person->getLivingStatus() === LivingStatus::DECEASED) {
			return;
		}

		$person->setLivingStatus(LivingStatus::DECEASED);
		$person->setUpdatedAt($this->timeFactory->getTime());
		$this->personMapper->update($person);
	}
}
