<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

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

	/**
	 * Find relationships involving a person.
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
	 * Delete a relationship.
	 */
	public function delete(
		Relationship|\OCP\AppFramework\Db\Entity $relationship,
	): \OCP\AppFramework\Db\Entity {
		return parent::delete($relationship);
	}
}
