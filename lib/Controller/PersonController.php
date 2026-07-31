<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\EventType;
use OCA\Kinship\Db\Person;
use OCA\Kinship\Service\EventService;
use OCA\Kinship\Service\PersonService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class PersonController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private PersonService $service,
		private EventService $eventService,
	) {
		parent::__construct(
			$appName,
			$request
		);
	}

	public function index(
		?string $searchQuery = null,
		?array $selected = [],
	): DataResponse {
		$people = $this->service->getAll([
			'searchQuery' => $searchQuery,
			'selected' => $selected,
		]);

		return new DataResponse([
			'people' => $people,
		]);
	}

	public function show(
		int $id,
	): DataResponse {
		return new DataResponse(
			$this->service->get($id)
		);
	}

	/**
	 * Create person. `birthDate` / `deathDate` in the payload are stored as
	 * birth/death events, never as person columns.
	 */
	public function create(
		array $data,
	): DataResponse {
		$instance = new Person();

		$instance->setFirstName($data['firstName'] ?? '');
		$instance->setMiddleName($data['middleName'] ?? null);
		$instance->setLastName($data['lastName'] ?? '');
		$instance->setPreferredName($data['preferredName'] ?? null);
		$instance->setSuffix($data['suffix'] ?? null);
		$instance->setGender($data['gender'] ?? null);
		$instance->setBiography($data['biography'] ?? null);
		$instance->setlivingStatus($data['livingStatus'] ?? null);

		if (isset($data['photoFileId'])) {
			$instance->setPhotoFileId((int)$data['photoFileId']);
		}

		$person = $this->service->create($instance);

		$this->saveLifeDates($person->getId(), $data);

		return new DataResponse(
			$this->service->hydrateLifeDates([$person])[0],
			Http::STATUS_CREATED
		);
	}

	public function update(
		int $id,
		array $data,
		EventService $eventService,
	): DataResponse {
		try {
			$person = $this->service->updatePerson($id, $data);

			$this->saveLifeDates($person->getId(), $data);

			return new DataResponse(
				$this->service->get($person->getId())
			);
		} catch (\Exception $exception) {
			return new DataResponse(
				[
					'error' => $exception->getMessage()
				]
			);
		}
	}

	/**
	 * Route the payload's birth/death dates to their events.
	 *
	 * @param array<string, mixed> $data
	 */
	private function saveLifeDates(
		int $personId,
		array $data,
	): void {
		foreach ([
			'birthDate' => EventType::BIRTH,
			'deathDate' => EventType::DEATH,
		] as $key => $type) {
			if (array_key_exists($key, $data)) {
				$this->eventService->setLifeDate($personId, $type, $data[$key]);
			}
		}
	}

	public function delete(
		int $id,
	): DataResponse {
		$this->service->deletePerson($id);

		return new DataResponse(['deleted' => true]);
	}
}
