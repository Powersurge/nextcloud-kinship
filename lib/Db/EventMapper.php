<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

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

	/**
	 * Find events for a person.
	 *
	 * @return Event[]
	 */
	public function findByPerson(
		int $personId,
	): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq(
					'person_id',
					$qb->createNamedParameter(
						$personId,
						IQueryBuilder::PARAM_INT
					)
				)
			);

		return $this->findEntities($qb);
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
	 * Create event.
	 */
	public function insert(
		Event|\OCP\AppFramework\Db\Entity $event,
	): Event {

		return parent::insert(
			$event
		);

	}

	/**
	 * Update event.
	 */
	public function update(
		Event|\OCP\AppFramework\Db\Entity $event,
	): Event {

		return parent::update(
			$event
		);

	}

	/**
	 * Delete an event.
	 */
	public function delete(
		Event|\OCP\AppFramework\Db\Entity $event,
	): \OCP\AppFramework\Db\Entity {
		return parent::delete($event);
	}
}
