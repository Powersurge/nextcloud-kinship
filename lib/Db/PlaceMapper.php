<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Place>
 */
class PlaceMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'kinship_places', Place::class);
	}

	/**
	 * @return Place[]
	 */
	public function findAll(): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->orderBy('name', 'ASC');

		return $this->findEntities($qb);
	}

	public function find(int $id): Place {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	public function findOrNull(int $id): ?Place {
		try {
			return $this->find($id);
		} catch (DoesNotExistException) {
			return null;
		}
	}

	public function insert(Place|Entity $place): Place {
		return parent::insert($place);
	}

	public function update(Place|Entity $place): Place {
		return parent::update($place);
	}

	public function delete(Place|Entity $place): Entity {
		return parent::delete($place);
	}
}
