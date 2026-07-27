<?php

declare(strict_types=1);

namespace OCA\Kinship\Import;

use OCA\Kinship\Constants\EventType;
use OCA\Kinship\Db\Person;
use OCA\Kinship\Service\EventService;
use OCA\Kinship\Service\PersonService;

/**
 * Persists extracted GEDCOM individuals as Kinship people and life events.
 */
class GedcomPersonImporter {
	public function __construct(
		private PersonService $personService,
		private EventService $eventService,
	) {
	}

	/**
	 * @param array<string, array<string, mixed>> $people
	 * @return array<string, int> gedcomId => kinship person id
	 */
	public function import(array $people): array {
		/** @var array<string, int> $map */
		$map = [];

		foreach ($people as $gedcomId => $data) {
			$person = new Person();
			$person->setFirstName((string)($data['firstName'] ?? 'Unknown'));
			$person->setMiddleName($data['middleName'] ?? null);
			$person->setLastName((string)($data['lastName'] ?? ''));
			$person->setPreferredName($data['preferredName'] ?? null);
			$person->setSuffix($data['suffix'] ?? null);
			$person->setGender($data['gender'] ?? null);
			$person->setLivingStatus((string)($data['livingStatus'] ?? 'unknown'));

			$person = $this->personService->create($person);
			$personId = $person->getId();

			if (!empty($data['birthDate'])) {
				$this->eventService->setLifeDate($personId, EventType::BIRTH, $data['birthDate']);
			}
			if (!empty($data['deathDate'])) {
				$this->eventService->setLifeDate($personId, EventType::DEATH, $data['deathDate']);
			}

			$map[$gedcomId] = $personId;
		}

		return $map;
	}
}
