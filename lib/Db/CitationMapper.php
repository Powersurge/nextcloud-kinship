<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Citation>
 */
class CitationMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'kinship_citations', Citation::class);
	}

	public function find(int $id): Citation {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	public function findOrNull(int $id): ?Citation {
		try {
			return $this->find($id);
		} catch (DoesNotExistException) {
			return null;
		}
	}

	/**
	 * @return Citation[]
	 */
	public function findBySource(int $sourceId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('source_id', $qb->createNamedParameter($sourceId, IQueryBuilder::PARAM_INT)));

		return $this->findEntities($qb);
	}

	/**
	 * @return Citation[]
	 */
	public function findBySubject(string $subjectType, int $subjectId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('subject_type', $qb->createNamedParameter($subjectType)))
			->andWhere($qb->expr()->eq('subject_id', $qb->createNamedParameter($subjectId, IQueryBuilder::PARAM_INT)));

		return $this->findEntities($qb);
	}

	public function deleteBySource(int $sourceId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('source_id', $qb->createNamedParameter($sourceId, IQueryBuilder::PARAM_INT)));
		$qb->executeStatement();
	}

	public function deleteBySubject(string $subjectType, int $subjectId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('subject_type', $qb->createNamedParameter($subjectType)))
			->andWhere($qb->expr()->eq('subject_id', $qb->createNamedParameter($subjectId, IQueryBuilder::PARAM_INT)));
		$qb->executeStatement();
	}

	public function countBySource(int $sourceId): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->func()->count('*'))
			->from($this->getTableName())
			->where($qb->expr()->eq('source_id', $qb->createNamedParameter($sourceId, IQueryBuilder::PARAM_INT)));

		return (int)$qb->executeQuery()->fetchOne();
	}

	public function insert(Citation|Entity $citation): Citation {
		return parent::insert($citation);
	}

	public function update(Citation|Entity $citation): Citation {
		return parent::update($citation);
	}

	public function delete(Citation|Entity $citation): Entity {
		return parent::delete($citation);
	}
}
