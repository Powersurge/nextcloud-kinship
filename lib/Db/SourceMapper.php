<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Source>
 */
class SourceMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'kinship_sources', Source::class);
	}

	/**
	 * @return Source[]
	 */
	public function findAll(): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->orderBy('title', 'ASC');

		return $this->findEntities($qb);
	}

	public function find(int $id): Source {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	public function findOrNull(int $id): ?Source {
		try {
			return $this->find($id);
		} catch (DoesNotExistException) {
			return null;
		}
	}

	public function insert(Source|Entity $source): Source {
		return parent::insert($source);
	}

	public function update(Source|Entity $source): Source {
		return parent::update($source);
	}

	public function delete(Source|Entity $source): Entity {
		return parent::delete($source);
	}
}
