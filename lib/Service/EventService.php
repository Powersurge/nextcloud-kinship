<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Db\Event;
use OCA\Kinship\Db\EventMapper;

class EventService {
	public function __construct(
		private EventMapper $mapper,
	) {
	}

	/**
	 * Get all events.
	 *
	 * @return Event[]
	 */
	public function getAll(): array {
		return $this->mapper->findAll();
	}

	/**
	 * Get an event by ID.
	 */
	public function get(
		int $id,
	): Event {
		return $this->mapper->find($id);
	}

	/**
	 * Get events for a person.
	 *
	 * @return Event[]
	 */
	public function getForPerson(
		int $personId,
	): array {
		return $this->mapper->findByPerson($personId);
	}

	/**
	 * Get events for a family.
	 *
	 * @return Event[]
	 */
	public function getForFamily(
		int $familyId,
	): array {
		return $this->mapper->findByFamily($familyId);
	}

	public function create(
		Event $event,
	): Event {

		return $this->mapper->insert(
			$event
		);

	}

	public function updateEvent(
		int $id,
		array $data,
	): Event {

		$event
			= $this->mapper->find(
				$id
			);

		if (isset($data['title'])) {

			$event->setTitle(
				$data['title']
			);

		}

		if (isset($data['type'])) {

			$event->setType(
				$data['type']
			);

		}

		if (isset($data['eventDate'])) {

			$event->setEventDate(
				$data['eventDate']
			);

		}

		if (isset($data['location'])) {

			$event->setLocation(
				$data['location']
			);

		}

		return $this->mapper->update(
			$event
		);
	}

	public function deleteEvent(
		int $id,
	): void {

		$event
			= $this->mapper->find(
				$id
			);

		$this->mapper->delete(
			$event
		);
	}
}
