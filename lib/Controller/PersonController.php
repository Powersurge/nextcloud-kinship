<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Db\DateValue;
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
	) {
		parent::__construct(
			$appName,
			$request
		);
	}

	public function index(): DataResponse {
		return new DataResponse([
			'people' => $this->service->getAll()
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
	 * Create person. Birth/death dates are recorded as birth/death events,
	 * not on the person, so they are not accepted here.
	 */
	public function create(
		array $data,
		EventService $eventService,
	): DataResponse {
		$instance = new Person();

		$instance->setFirstName($data['firstName'] ?? '');
		$instance->setMiddleName($data['middleName'] ?? null);
		$instance->setLastName($data['lastName'] ?? '');
		$instance->setPreferredName($data['preferredName'] ?? null);
		$instance->setSuffix($data['suffix'] ?? null);
		$instance->setGender($data['gender'] ?? null);
		$instance->setBiography($data['biography'] ?? null);

		if (isset($data['photoFileId'])) {
			$instance->setPhotoFileId((int)$data['photoFileId']);
		}

		$person = $this->service->create($instance);

		if (isset($data['birthDate'])) {
			$eventService->create([
				'type' => 'birth',
				'date' => $data['birthDate'],
				'participants' => [
					[
						'personId' => $person->getId(),
						'role' => 'subject'
					]
				]
			]);
		}

		return new DataResponse(
			$person,
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

            if (isset($data['birthDate'])) {
                $eventService->updateEvent([
                    'type' => 'birth',
                    'date' => $data['birthDate'],
                    'participants' => [
                        [
                            'personId' => $person->getId(),
                            'role' => 'subject'
                        ]
                    ]
                ]);
            }

            return new DataResponse(
                $person
            );
        } catch (\Exception $exception) {
            return new DataResponse(
                [
                    'error' => $exception->getMessage()
                ]
            );
        }
	}

	public function delete(
		int $id,
	): DataResponse {
		$this->service->deletePerson($id);

		return new DataResponse(['deleted' => true]);
	}
}
