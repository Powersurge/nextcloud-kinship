<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Db\Person;
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
	 * Create person.
	 */
	public function create(
		array $data,
	): DataResponse {
		$person = new Person();

		$person->setFirstName(
			$data['firstName'] ?? ''
		);

		$person->setMiddleName(
			$data['middleName'] ?? ''
		);

		$person->setLastName(
			$data['lastName'] ?? ''
		);

		$person->setPreferredName(
			$data['preferredName'] ?? ''
		);

		$person->setBirthDate(
			$data['birthDate'] ?? null
		);

		$person->setDeathDate(
			$data['deathDate'] ?? null
		);

		$person->setGender(
			$data['gender'] ?? null
		);

		$person->setAlive(
			$data['alive'] ?? true
		);

		$person->setBiography(
			$data['biography'] ?? null
		);

		return new DataResponse(
			$this->service->create($person),
			Http::STATUS_CREATED
		);
	}

	/**
	 * Update person.
	 */
	public function update(
		int $id,
		array $data,
	): DataResponse {
		return new DataResponse(
			$this->service->updatePerson(
				$id,
				$data
			)
		);
	}

	/**
	 * Delete person.
	 */
	public function delete(
		int $id,
	): DataResponse {
		$this->service->deletePerson($id);

		return new DataResponse(
			[
				'deleted' => true
			]
		);
	}
}
