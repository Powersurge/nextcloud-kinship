<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Db\Event;
use OCA\Kinship\Service\EventService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class EventController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private EventService $service,
	) {
		parent::__construct(
			$appName,
			$request
		);
	}

	/**
	 * Return all events.
	 */
	public function index(): DataResponse {
		return new DataResponse([
			'events' => $this->service->getAll()
		]);
	}

	/**
	 * Return one event.
	 */
	public function show(
		int $id,
	): DataResponse {
		return new DataResponse(
			$this->service->get($id)
		);
	}

	/**
	 * Return events for a person.
	 */
	public function personEvents(
		int $personId,
	): DataResponse {
		return new DataResponse([
			'events'
				=> $this->service->getForPerson($personId)
		]);
	}

	/**
	 * Return events for a family.
	 */
	public function familyEvents(
		int $familyId,
	): DataResponse {
		return new DataResponse([
			'events'
				=> $this->service->getForFamily($familyId)
		]);
	}

	public function create(
		array $data,
	): DataResponse {

		$event
			= new Event();

		$event->setTitle(
			$data['title'] ?? ''
		);

		$event->setType(
			$data['type'] ?? 'custom'
		);

		$event->setEventDate(
			$data['eventDate'] ?? null
		);

		$event->setLocation(
			$data['location'] ?? null
		);

		if (
			isset($data['personId'])
		) {

			$event->setPersonId(
				$data['personId']
			);

		}

		if (
			isset($data['familyId'])
		) {

			$event->setFamilyId(
				$data['familyId']
			);
		}

		return new DataResponse(
			$this->service->create(
				$event
			)
		);
	}

	public function update(
		int $id,
		array $data,
	): DataResponse {

		return new DataResponse(
			$this->service->updateEvent(
				$id,
				$data
			)
		);

	}

	public function delete(
		int $id,
	): DataResponse {

		$this->service->deleteEvent(
			$id
		);

		return new DataResponse([
			'deleted' => true
		]);

	}
}
