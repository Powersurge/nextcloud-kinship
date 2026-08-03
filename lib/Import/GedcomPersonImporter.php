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
	 * @return array{
	 *   people: array<string, int>,
	 *   events: array<string, array{birth: ?int, death: ?int}>
	 * }
	 */
	public function import(array $people): array {
		/** @var array<string, int> $peopleMap */
		$peopleMap = [];
		/** @var array<string, array{birth: ?int, death: ?int}> $eventsMap */
		$eventsMap = [];

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
			$peopleMap[$gedcomId] = $personId;

			$birthEventId = null;
			$deathEventId = null;

			if (!empty($data['birthDate'])) {
				$birthEvent = $this->eventService->setLifeDate($personId, EventType::BIRTH, $data['birthDate']);
				$birthEventId = $birthEvent?->getId();
			}
			if (!empty($data['deathDate'])) {
				$deathEvent = $this->eventService->setLifeDate($personId, EventType::DEATH, $data['deathDate']);
				$deathEventId = $deathEvent?->getId();
			}

			$eventsMap[$gedcomId] = [
				'birth' => $birthEventId,
				'death' => $deathEventId,
			];
		}

		return [
			'people' => $peopleMap,
			'events' => $eventsMap,
		];
	}
}
