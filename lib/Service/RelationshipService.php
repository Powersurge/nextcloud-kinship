<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Db\Relationship;
use OCA\Kinship\Db\RelationshipMapper;

class RelationshipService {
	public function __construct(
		private RelationshipMapper $mapper,
	) {
	}

	/**
	 * Get all relationships.
	 *
	 * @return Relationship[]
	 */
	public function getAll(): array {
		return $this->mapper->findAll();
	}

	/**
	 * Get relationship by ID.
	 */
	public function get(
		int $id,
	): Relationship {
		return $this->mapper->find($id);
	}

	/**
	 * Get all relationships for a person.
	 *
	 * @return Relationship[]
	 */
	public function getForPerson(
		int $personId,
	): array {
		return $this->mapper->findByPerson($personId);
	}

	/**
	 * Delete a relationship.
	 */
	public function delete(
		Relationship $relationship,
	): void {
		$this->mapper->delete($relationship);
	}
}
