<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<CustomEvent>
 */
class CustomEventMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'kinship_custom_events', CustomEvent::class);
	}

	/**
	 * @return CustomEvent[]
	 */
	public function findAll(): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->orderBy('name', 'ASC');

		return $this->findEntities($qb);
	}

	public function find(int $id): CustomEvent {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	public function findOrNull(int $id): ?CustomEvent {
		try {
			return $this->find($id);
		} catch (DoesNotExistException) {
			return null;
		}
	}

	/**
	 * @return CustomEvent[]
	 */
	public function findByOwner(string $ownerUid): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('owner_uid', $qb->createNamedParameter($ownerUid)))
			->orderBy('name', 'ASC');

		return $this->findEntities($qb);
	}

	public function insert(CustomEvent|Entity $customEvent): CustomEvent {
		return parent::insert($customEvent);
	}

	public function update(CustomEvent|Entity $customEvent): CustomEvent {
		return parent::update($customEvent);
	}

	public function delete(CustomEvent|Entity $customEvent): Entity {
		return parent::delete($customEvent);
	}
}
