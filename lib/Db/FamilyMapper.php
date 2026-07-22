<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class FamilyMapper extends QBMapper {
	public function __construct(
		IDBConnection $db,
	) {
		parent::__construct(
			$db,
			'kinship_families',
			Family::class
		);
	}

	/**
	 * Return all families.
	 *
	 * @return Family[]
	 */
	public function findAll(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());

		return $this->findEntities($qb);
	}

	/**
	 * Find a family by ID.
	 */
	public function find(
		int $id,
	): Family {
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
	 * Create family.
	 */
	public function insert(
		Family|\OCP\AppFramework\Db\Entity $family,
	): Family {
		return parent::insert($family);
	}

	/**
	 * Update family.
	 */
	public function update(
		Family|\OCP\AppFramework\Db\Entity $family,
	): Family {
		return parent::update($family);
	}

	/**
	 * Delete a family.
	 */
	public function delete(
		Family|\OCP\AppFramework\Db\Entity $family,
	): \OCP\AppFramework\Db\Entity {
		return parent::delete($family);
	}
}
