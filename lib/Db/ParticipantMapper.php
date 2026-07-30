<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCA\Kinship\Constants\ParticipantRole;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Participant>
 */
class ParticipantMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'kinship_participants', Participant::class);
	}

	public function find(int $id): Participant {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	public function findOrNull(int $id): ?Participant {
		try {
			return $this->find($id);
		} catch (DoesNotExistException) {
			return null;
		}
	}

	/**
	 * @return Participant[]
	 */
	public function findByEvent(int $eventId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('event_id', $qb->createNamedParameter($eventId, IQueryBuilder::PARAM_INT)));

		return $this->findEntities($qb);
	}

	public function findSubjectByEvent(int $eventId): Participant|Entity {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('event_id', $qb->createNamedParameter($eventId, IQueryBuilder::PARAM_INT)))
			->andWhere(
				$qb->expr()->eq('role', $qb->createNamedParameter(ParticipantRole::SUBJECT, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntity($qb);
	}

	/**
	 * @return Participant[]
	 */
	public function findByPerson(int $personId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('person_id', $qb->createNamedParameter($personId, IQueryBuilder::PARAM_INT)));

		return $this->findEntities($qb);
	}

	public function insert(Participant|Entity $participant): Participant {
		return parent::insert($participant);
	}

	public function update(Participant|Entity $participant): Participant {
		return parent::update($participant);
	}

	public function delete(Participant|Entity $participant): Entity {
		return parent::delete($participant);
	}
}
