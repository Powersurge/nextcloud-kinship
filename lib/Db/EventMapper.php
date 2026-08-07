<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use DateTimeImmutable;
use OCA\Kinship\Constants\EventType;
use OCA\Kinship\Constants\LivingStatus;
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
	 * First event using this Nextcloud file as primary photo (for proxy authorization).
	 */
	public function findFirstByPhotoFileId(int $fileId): ?Event {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('photo_file_id', $qb->createNamedParameter($fileId, IQueryBuilder::PARAM_INT)))
			->setMaxResults(1);

		try {
			return $this->findEntity($qb);
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

	public function findBirthEventsThatExceededAgeLimit(int $ageThreshold = 110): array {
		$qb = $this->db->getQueryBuilder();

		$cutoffYear = (int)new DateTimeImmutable('today')
			->modify("-{$ageThreshold} years")
			->format('Y');

		$qb->select('e.*')
			->from($this->getTableName(), 'e')
			->innerJoin(
				'e',
				'kinship_participants',
				'pr',
				$qb->expr()->eq('pr.event_id', 'e.id')
			)
			->innerJoin(
				'pr',
				'kinship_persons',
				'p',
				$qb->expr()->eq('p.id', 'pr.person_id')
			)
			->where(
				$qb->expr()->eq(
					'p.living_status',
					$qb->createNamedParameter(LivingStatus::LIVING)
				)
			)
			->andWhere(
				$qb->expr()->eq(
					'type',
					$qb->createNamedParameter(EventType::BIRTH)
				)
			)
			->andWhere(
				$qb->expr()->lte(
					'date_start_year',
					$qb->createNamedParameter($cutoffYear, IQueryBuilder::PARAM_INT)
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
		?string $type = null,
	): array {
		$qb = $this->createFindByPersonQueryBuilder($personId, $type);
		$qb->select('e.*');

		return $this->findEntities($qb);
	}

	/**
	 * @return Event[]
	 */
	public function findByPersonPaginated(
		int $personId,
		int $limit,
		int $offset,
		?string $type = null,
	): array {
		$qb = $this->createFindByPersonQueryBuilder($personId, $type);
		$qb->select('e.*')
			->setMaxResults($limit)
			->setFirstResult($offset);

		$this->applyPersonEventOrdering($qb);

		return $this->findEntities($qb);
	}

	public function countByPerson(int $personId, ?string $type = null): int {
		$qb = $this->createFindByPersonQueryBuilder($personId, $type);
		$qb->select($qb->createFunction('COUNT(*)'));

		return (int)$qb->executeQuery()->fetchOne();
	}

	private function createFindByPersonQueryBuilder(
		int $personId,
		?string $type = null,
	): IQueryBuilder {
		$qb = $this->db->getQueryBuilder();

		$qb->from($this->getTableName(), 'e')
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

		if ($type !== null) {
			$qb->andWhere(
				$qb->expr()->eq(
					'e.type',
					$qb->createNamedParameter($type)
				)
			);
		}

		return $qb;
	}

	private function applyPersonEventOrdering(IQueryBuilder $qb): void {
		$qb->addOrderBy('e.date_start_year', 'ASC')
			->addOrderBy('e.date_start_month', 'ASC')
			->addOrderBy('e.date_start_day', 'ASC')
			->addOrderBy('e.id', 'ASC');
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
