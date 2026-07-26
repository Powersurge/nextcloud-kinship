<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\EventType;
use OCA\Kinship\Constants\LivingStatus;
use OCA\Kinship\Db\EventMapper;
use OCA\Kinship\Db\Person;
use OCA\Kinship\Db\PersonMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

/**
 * People CRUD. Birth/death dates are not stored on the person: they are read
 * from the person's birth/death events on the way out (hydrateLifeDates) and
 * written through EventService::setLifeDate on the way in.
 */
class PersonService {
	public function __construct(
		private PersonMapper $mapper,
		private EventMapper $eventMapper,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	/**
	 * @return Person[]
	 */
	public function getAll(): array {
		return $this->hydrateLifeDates($this->mapper->findAll());
	}

	public function get(int $id): Person {
		$person = $this->mapper->find($id);

		//		$person->setEvents($this->eventMapper->findByPerson($id));

		return $this->hydrateLifeDates([$person])[0];
	}

	/**
	 * Fill in birthDate/deathDate from the birth/death events of the given
	 * people, in one query for the whole set.
	 *
	 * @param Person[] $people
	 * @return Person[]
	 */
	public function hydrateLifeDates(array $people): array {
		$ids = [];
		foreach ($people as $person) {
			$ids[] = $person->getId();
		}

		$lifeEvents = $this->eventMapper->findLifeEventsByPersons($ids);

		foreach ($people as $person) {
			$events = $lifeEvents[$person->getId()] ?? [];
			$birth = $events[EventType::BIRTH] ?? null;
			$death = $events[EventType::DEATH] ?? null;

			$person->setEvents($this->eventMapper->findByPerson($person->getId()));

			$person->setBirthDate($birth?->getDate());
			$person->setDeathDate($death?->getDate());
		}

		return $people;
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
