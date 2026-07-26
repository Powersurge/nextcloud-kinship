<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Claim>
 */
class ClaimMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'kinship_claims', Claim::class);
	}

	/**
	 * @return Claim[]
	 */
	public function findAll(): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->orderBy('created_at', 'DESC');

		return $this->findEntities($qb);
	}

	public function find(int $id): Claim {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	public function findOrNull(int $id): ?Claim {
		try {
			return $this->find($id);
		} catch (DoesNotExistException) {
			return null;
		}
	}

	/**
	 * @return Claim[]
	 */
	public function findBySubject(string $subjectType, int $subjectId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('subject_type', $qb->createNamedParameter($subjectType)))
			->andWhere($qb->expr()->eq('subject_id', $qb->createNamedParameter($subjectId, IQueryBuilder::PARAM_INT)));

		return $this->findEntities($qb);
	}

	/**
	 * @return Claim[]
	 */
	public function findByStatus(string $status): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('status', $qb->createNamedParameter($status)));

		return $this->findEntities($qb);
	}

	public function insert(Claim|Entity $claim): Claim {
		return parent::insert($claim);
	}

	public function update(Claim|Entity $claim): Claim {
		return parent::update($claim);
	}

	public function delete(Claim|Entity $claim): Entity {
		return parent::delete($claim);
	}
}
