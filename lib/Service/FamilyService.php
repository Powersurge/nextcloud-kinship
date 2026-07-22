<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Db\Family;
use OCA\Kinship\Db\FamilyMapper;

class FamilyService {
	public function __construct(
		private FamilyMapper $mapper,
	) {
	}

	/**
	 * Get all families.
	 *
	 * @return Family[]
	 */
	public function getAll(): array {
		return $this->mapper->findAll();
	}

	/**
	 * Get a family by ID.
	 */
	public function get(
		int $id,
	): Family {
		return $this->mapper->find($id);
	}

	/**
	 * Create family.
	 */
	public function create(
		Family $family,
	): Family {
		return $this->mapper->insert($family);
	}

	/**
	 * Update family.
	 */
	public function updateFamily(
		int $id,
		array $data,
	): Family {

		$family
			= $this->mapper->find($id);

		if (isset($data['name'])) {
			$family->setName(
				$data['name']
			);
		}

		if (isset($data['description'])) {
			$family->setDescription(
				$data['description']
			);
		}

		return $this->mapper->update(
			$family
		);
	}

	/**
	 * Delete family.
	 */
	public function deleteFamily(
		int $id,
	): void {

		$family
			= $this->mapper->find($id);

		$this->mapper->delete(
			$family
		);
	}
}
