<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Db\Person;
use OCA\Kinship\Db\PersonMapper;

class PersonService {
	public function __construct(
		private PersonMapper $mapper,
	) {
	}

	/**
	 * Get all people.
	 *
	 * @return Person[]
	 */
	public function getAll(): array {
		return $this->mapper->findAll();
	}

	/**
	 * Get a person by ID.
	 */
	public function get(int $id): Person {
		return $this->mapper->find($id);
	}

	/**
	 * Delete a person.
	 */
	public function delete(Person $person): void {
		$this->mapper->delete($person);
	}

	/**
	 * Create a person.
	 */
	public function create(
		Person $person,
	): Person {
		return $this->mapper->insert($person);
	}

	/**
	 * Update a person.
	 */
	public function update(
		Person $person,
	): Person {
		return $this->mapper->update($person);
	}

	/**
	 * Update person fields.
	 */
	public function updatePerson(
		int $id,
		array $data,
	): Person {
		$person = $this->mapper->find($id);

		if (isset($data['firstName'])) {
			$person->setFirstName(
				$data['firstName']
			);
		}

		if (isset($data['middleName'])) {
			$person->setMiddleName(
				$data['middleName']
			);
		}

		if (isset($data['lastName'])) {
			$person->setLastName(
				$data['lastName']
			);
		}

		if (isset($data['preferredName'])) {
			$person->setPreferredName(
				$data['preferredName']
			);
		}

		if (isset($data['birthDate'])) {
			$person->setBirthDate(
				$data['birthDate']
			);
		}

		if (array_key_exists('deathDate', $data)) {
			$person->setDeathDate(
				$data['deathDate']
			);
		}

		if (array_key_exists('gender', $data)) {
			$person->setGender(
				$data['gender']
			);
		}

		if (isset($data['alive'])) {
			$person->setAlive(
				(bool)$data['alive']
			);
		}

		if (array_key_exists('biography', $data)) {
			$person->setBiography(
				$data['biography']
			);
		}

		return $this->mapper->update($person);
	}

	/**
	 * Delete person.
	 */
	public function deletePerson(
		int $id,
	): void {
		$person = $this->mapper->find($id);

		$this->mapper->delete($person);
	}
}
