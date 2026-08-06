<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCP\AppFramework\Db\DoesNotExistException;
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
	public function findAll(array $queryParams = []): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());

		if (!empty($queryParams['selected'])) {
			$qb->where(
				$qb->expr()->in(
					'id',
					$qb->createNamedParameter($queryParams['selected'], IQueryBuilder::PARAM_INT_ARRAY)
				)
			);
		}

		if (!empty($queryParams['searchQuery'])) {
			$fulltextQuery = $this->buildFulltextQuery($queryParams['searchQuery']);

			if ($fulltextQuery !== '') {
				$match = $qb->createFunction(
					'MATCH(`first_name`,`middle_name`,`last_name`,`suffix`,`preferred_name`) AGAINST('
					. $qb->createNamedParameter($fulltextQuery, IQueryBuilder::PARAM_STR)
					. ' IN BOOLEAN MODE)'
				);
				$qb->where(
					$qb->expr()->gt(
						$match,
						$qb->createNamedParameter(0, IQueryBuilder::PARAM_INT)
					)
				);
			}
		}

		$qb->orderBy('first_name', 'ASC');

		return $this->findEntities($qb);
	}

	private function buildFulltextQuery(string $searchQuery): string {
		$terms = preg_split('/\s+/', trim($searchQuery), -1, PREG_SPLIT_NO_EMPTY);
		$parts = [];
		foreach ($terms as $term) {
			$term = trim(preg_replace('/[+\-><()~*"@]+/', ' ', $term) ?? '');
			if ($term !== '') {
				$parts[] = '+' . $term . '*';
			}
		}

		return implode(' ', $parts);
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
		} catch (DoesNotExistException $e) {
			return null;
		}
	}

	/**
	 * First person using this Nextcloud file as primary photo (for proxy authorization).
	 */
	public function findFirstByPhotoFileId(int $fileId): ?Person {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('photo_file_id', $qb->createNamedParameter($fileId, IQueryBuilder::PARAM_INT)))
			->setMaxResults(1);

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException) {
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
