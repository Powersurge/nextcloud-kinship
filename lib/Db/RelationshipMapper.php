<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Relationship>
 */
class RelationshipMapper extends QBMapper {
	public function __construct(
		IDBConnection $db,
	) {
		parent::__construct(
			$db,
			'kinship_relationships',
			Relationship::class
		);
	}

	/**
	 * Return all relationships.
	 *
	 * @return Relationship[]
	 */
	public function findAll(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());

		return $this->findEntities($qb);
	}

	/**
	 * Find a relationship by ID.
	 */
	public function find(
		int $id,
	): Relationship {
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

	public function findOrNull(int $id): ?Relationship {
		try {
			return $this->find($id);
		} catch (DoesNotExistException) {
			return null;
		}
	}

	/**
	 * Find relationships involving a person (either side).
	 *
	 * @return Relationship[]
	 */
	public function findByPerson(
		int $personId,
	): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->orX(
					$qb->expr()->eq(
						'person_a',
						$qb->createNamedParameter(
							$personId,
							IQueryBuilder::PARAM_INT
						)
					),
					$qb->expr()->eq(
						'person_b',
						$qb->createNamedParameter(
							$personId,
							IQueryBuilder::PARAM_INT
						)
					)
				)
			);

		return $this->findEntities($qb);
	}

	/**
	 * Find an existing canonical row for the exact (person_a, person_b, type)
	 * triple, used to dedupe on write. Expects already-canonicalized ids.
	 */
	public function findCanonical(
		int $personA,
		int $personB,
		string $type,
	): ?Relationship {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('person_a', $qb->createNamedParameter($personA, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('person_b', $qb->createNamedParameter($personB, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('type', $qb->createNamedParameter($type)));

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException) {
			return null;
		}
	}

	/**
	 * All relationships directly between two people, in either direction.
	 *
	 * @return Relationship[]
	 */
	public function findBetween(
		int $personX,
		int $personY,
	): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->orX(
					$qb->expr()->andX(
						$qb->expr()->eq('person_a', $qb->createNamedParameter($personX, IQueryBuilder::PARAM_INT)),
						$qb->expr()->eq('person_b', $qb->createNamedParameter($personY, IQueryBuilder::PARAM_INT)),
					),
					$qb->expr()->andX(
						$qb->expr()->eq('person_a', $qb->createNamedParameter($personY, IQueryBuilder::PARAM_INT)),
						$qb->expr()->eq('person_b', $qb->createNamedParameter($personX, IQueryBuilder::PARAM_INT)),
					),
				)
			);

		return $this->findEntities($qb);
	}

	public function insert(
		Relationship|Entity $relationship,
	): Relationship {
		return parent::insert($relationship);
	}

	public function update(
		Relationship|Entity $relationship,
	): Relationship {
		return parent::update($relationship);
	}

	/**
	 * Delete a relationship.
	 */
	public function delete(
		Relationship|Entity $relationship,
	): Entity {
		return parent::delete($relationship);
	}
}
