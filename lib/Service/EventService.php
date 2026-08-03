<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\EventType;
use OCA\Kinship\Constants\ParticipantRole;
use OCA\Kinship\Constants\SubjectType;
use OCA\Kinship\Db\DateValue;
use OCA\Kinship\Db\Event;
use OCA\Kinship\Db\EventMapper;
use OCA\Kinship\Db\Participant;
use OCA\Kinship\Db\ParticipantMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

/**
 * Events CRUD. Persons are attached via participants. Birth and death dates
 * live only here: nothing is copied onto the person; living_status is derived
 * from life events via LivingStatusService.
 */
class EventService {
	public function __construct(
		private EventMapper $mapper,
		private ParticipantMapper $participantMapper,
		private PlaceService $placeService,
		private CitationService $citationService,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
		private LivingStatusService $livingStatusService,
	) {
	}

	/**
	 * @return Event[]
	 */
	public function getAll(): array {
		return $this->hydratePlaces($this->mapper->findAll());
	}

	public function get(int $id): Event {
		return $this->hydratePlaces([$this->mapper->find($id)])[0];
	}

	/**
	 * @return array{events: Event[], total: int, limit: int, offset: int}
	 */
	public function getForPersonPaginated(
		int $personId,
		int $limit,
		int $offset,
		?string $type = null,
	): array {
		$limit = max(1, min(50, $limit));
		$offset = max(0, $offset);

		return [
			'events' => $this->hydratePlaces(
				$this->mapper->findByPersonPaginated($personId, $limit, $offset, $type)
			),
			'total' => $this->mapper->countByPerson($personId, $type),
			'limit' => $limit,
			'offset' => $offset,
		];
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

		return $this->hydratePlaces($events);
	}

	/**
	 * @return Event[]
	 */
	public function getForFamily(int $familyId): array {
		return $this->hydratePlaces($this->mapper->findByFamily($familyId));
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

		return $this->hydratePlaces([$event])[0];
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

		return $this->hydratePlaces([$event])[0];
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

		$this->citationService->deleteBySubject(SubjectType::EVENT, $id);
		$this->mapper->delete($event);

		if ($event->getType() === EventType::DEATH || $event->getType() === EventType::BIRTH) {
			foreach (array_keys($affected) as $personId) {
				$this->livingStatusService->syncPersonLivingStatus($personId);
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

	/**
	 * @param Event[] $events
	 * @return Event[]
	 */
	private function hydratePlaces(array $events): array {
		$summaries = $this->placeService->summarizeByIds(
			array_map(static fn (Event $event) => $event->getPlaceId(), $events)
		);

		foreach ($events as $event) {
			$placeId = $event->getPlaceId();
			$event->setPlace($placeId !== null ? ($summaries[$placeId] ?? null) : null);
		}

		return $events;
	}

	private function syncAffectedPersons(Event $event): void {
		if (!in_array($event->getType(), [EventType::BIRTH, EventType::DEATH], true)) {
			return;
		}

		foreach ($this->participantMapper->findByEvent($event->getId()) as $participant) {
			if ($participant->getRole() === ParticipantRole::SUBJECT) {
				$this->livingStatusService->syncPersonLivingStatus($participant->getPersonId());
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
	public function setLifeDate(int $personId, string $type, ?array $date): ?Event {
		if (!in_array($type, [EventType::BIRTH, EventType::DEATH], true)) {
			return null;
		}

		$event = $this->mapper->findLifeEvent($personId, $type);

		if ($event !== null) {
			return $this->updateEvent($event->getId(), ['date' => $date]);
		}

		if (DateValue::fromArray($date)->isEmpty()) {
			return null;
		}

		return $this->create([
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
}
