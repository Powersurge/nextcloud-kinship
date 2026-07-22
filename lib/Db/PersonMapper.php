<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class PersonMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'kinship_persons', Person::class);
	}

	/**
	 * Return all people.
	 *
	 * @return Person[]
	 */
	public function findAll(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());

		return $this->findEntities($qb);
	}

	/**
	 * Find a person by ID.
	 *
	 * @param int $id
	 * @return Person
	 */
	public function find(int $id): Person {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq(
					'id',
					$qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)
				)
			);

		return $this->findEntity($qb);
	}

	/**
	 * Find person or return null.
	 */
	public function findOrNull(
		int $id,
	): ?Person {
		try {
			return $this->find($id);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			return null;
		}
	}

	/**
	 * Delete a person.
	 *
	 * @param Person $person
	 */
	public function delete(Person|\OCP\AppFramework\Db\Entity $person): \OCP\AppFramework\Db\Entity {
		return parent::delete($person);
	}

	/**
	 * Create a person.
	 */
	public function insert(
		Person|\OCP\AppFramework\Db\Entity $person,
	): Person {
		return parent::insert($person);
	}

	/**
	 * Update a person.
	 */
	public function update(
		Person|\OCP\AppFramework\Db\Entity $person,
	): Person {
		return parent::update($person);
	}
}
