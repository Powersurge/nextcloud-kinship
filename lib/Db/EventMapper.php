<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCA\Kinship\Constants\EventType;
use OCA\Kinship\Constants\ParticipantRole;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Event>
 */
class EventMapper extends QBMapper {
	public function __construct(
		IDBConnection $db,
	) {
		parent::__construct(
			$db,
			'kinship_events',
			Event::class
		);
	}

	/**
	 * Return all events.
	 *
	 * @return Event[]
	 */
	public function findAll(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());

		return $this->findEntities($qb);
	}

	/**
	 * Find an event by ID.
	 */
	public function find(
		int $id,
	): Event {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq(
					'id',
					$qb->createNamedParameter(
						$id,
						IQueryBuilder::PARAM_INT
					)
				)
			);

		return $this->findEntity($qb);
	}

	public function findOrNull(int $id): ?Event {
		try {
			return $this->find($id);
		} catch (DoesNotExistException) {
			return null;
		}
	}

	/**
	 * Find events for a family.
	 *
	 * @return Event[]
	 */
	public function findByFamily(
		int $familyId,
	): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq(
					'family_id',
					$qb->createNamedParameter(
						$familyId,
						IQueryBuilder::PARAM_INT
					)
				)
			);

		return $this->findEntities($qb);
	}

	/**
	 * Find events by core type.
	 *
	 * @return Event[]
	 */
	public function findByType(
		string $type,
	): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq(
					'type',
					$qb->createNamedParameter($type)
				)
			);

		return $this->findEntities($qb);
	}

	public function findByPerson(
		int $personId,
	): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('e.*')
			->from($this->getTableName(), 'e')
			->innerJoin(
				'e',
				'kinship_participants',
				'p',
				$qb->expr()->eq('p.event_id', 'e.id')
			)
			->where(
				$qb->expr()->eq(
					'p.person_id',
					$qb->createNamedParameter(
						$personId,
						IQueryBuilder::PARAM_INT
					)
				)
			)
			->andWhere(
				$qb->expr()->eq(
					'p.role',
					$qb->createNamedParameter(ParticipantRole::SUBJECT)
				)
			);

		return $this->findEntities($qb);
	}

	/**
	 * Birth/death events of the given people, keyed personId => type => Event.
	 *
	 * Joined through kinship_participants on the `subject` role, so only the
	 * person the event is about counts (not witnesses or officiants). A person
	 * is expected to have at most one birth and one death event; if several
	 * exist the last row read wins.
	 *
	 * @param int[] $personIds
	 * @return array<int, array<string, Event>>
	 */
	public function findLifeEventsByPersons(
		array $personIds,
	): array {
		if ($personIds === []) {
			return [];
		}

		$qb = $this->db->getQueryBuilder();

		$qb->select('e.*')
			->addSelect('p.person_id')
			->from($this->getTableName(), 'e')
			->innerJoin(
				'e',
				'kinship_participants',
				'p',
				$qb->expr()->eq('p.event_id', 'e.id')
			)
			->where(
				$qb->expr()->in(
					'p.person_id',
					$qb->createNamedParameter($personIds, IQueryBuilder::PARAM_INT_ARRAY)
				)
			)
			->andWhere(
				$qb->expr()->eq(
					'p.role',
					$qb->createNamedParameter(ParticipantRole::SUBJECT)
				)
			)
			->andWhere(
				$qb->expr()->in(
					'e.type',
					$qb->createNamedParameter(
						[EventType::BIRTH, EventType::DEATH],
						IQueryBuilder::PARAM_STR_ARRAY
					)
				)
			);

		$result = $qb->executeQuery();

		$events = [];
		while ($row = $result->fetch()) {
			$personId = (int)$row['person_id'];
			unset($row['person_id']);
			$events[$personId][(string)$row['type']] = Event::fromRow($row);
		}
		$result->closeCursor();

		return $events;
	}

	/**
	 * A person's birth or death event, or null when they have none.
	 */
	public function findLifeEvent(
		int $personId,
		string $type,
	): ?Event {
		return $this->findLifeEventsByPersons([$personId])[$personId][$type] ?? null;
	}

	/**
	 * Create event.
	 */
	public function insert(
		Event|Entity $event,
	): Event {

		return parent::insert(
			$event
		);

	}

	/**
	 * Update event.
	 */
	public function update(
		Event|Entity $event,
	): Event {

		return parent::update(
			$event
		);

	}

	/**
	 * Delete an event.
	 */
	public function delete(
		Event|Entity $event,
	): Entity {
		return parent::delete($event);
	}
}
