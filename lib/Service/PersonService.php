<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\LivingStatus;
use OCA\Kinship\Db\Person;
use OCA\Kinship\Db\PersonMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

/**
 * People CRUD. Birth/death dates are cached here for fast display and living
 * status but are owned by their birth/death events; EventService keeps the
 * cache in sync (see EventService::syncPersonLifeDates).
 */
class PersonService {
	public function __construct(
		private PersonMapper $mapper,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	/**
	 * @return Person[]
	 */
	public function getAll(): array {
		return $this->mapper->findAll();
	}

	public function get(int $id): Person {
		return $this->mapper->find($id);
	}

	public function delete(Person $person): void {
		$this->mapper->delete($person);
	}

	/**
	 * Create a person, stamping ownership and timestamps.
	 */
	public function create(Person $person): Person {
		$now = $this->timeFactory->getTime();
		$uid = $this->userSession->getUser()?->getUID();

		$person->setOwnerUid($uid);
		$person->setCreatedBy($uid);
		$person->setCreatedAt($now);
		$person->setUpdatedAt($now);

		return $this->mapper->insert($person);
	}

	public function update(Person $person): Person {
		$person->setUpdatedAt($this->timeFactory->getTime());

		return $this->mapper->update($person);
	}

	/**
	 * Update person fields from a request payload.
	 */
	public function updatePerson(int $id, array $data): Person {
		$person = $this->mapper->find($id);

		foreach ([
			'firstName' => 'setFirstName',
			'middleName' => 'setMiddleName',
			'lastName' => 'setLastName',
			'preferredName' => 'setPreferredName',
			'suffix' => 'setSuffix',
			'gender' => 'setGender',
			'biography' => 'setBiography',
		] as $key => $setter) {
			if (array_key_exists($key, $data)) {
				$person->$setter($data[$key]);
			}
		}

		if (array_key_exists('livingStatus', $data) && LivingStatus::isValid((string)$data['livingStatus'])) {
			$person->setLivingStatus($data['livingStatus']);
		}

		if (array_key_exists('photoFileId', $data)) {
			$person->setPhotoFileId($data['photoFileId'] !== null ? (int)$data['photoFileId'] : null);
		}

		$person->setUpdatedAt($this->timeFactory->getTime());

		return $this->mapper->update($person);
	}

	public function deletePerson(int $id): void {
		$this->mapper->delete($this->mapper->find($id));
	}
}
