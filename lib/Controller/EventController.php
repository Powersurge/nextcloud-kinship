<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Service\EventService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
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

	public function index(): DataResponse {
		return new DataResponse([
			'events' => $this->service->getAll()
		]);
	}

	public function show(
		int $id,
	): DataResponse {
		return new DataResponse(
			$this->service->get($id)
		);
	}

	public function personEvents(
		int $personId,
	): DataResponse {
		return new DataResponse([
			'events' => $this->service->getForPerson($personId)
		]);
	}

	public function familyEvents(
		int $familyId,
	): DataResponse {
		return new DataResponse([
			'events' => $this->service->getForFamily($familyId)
		]);
	}

	public function participants(
		int $id,
	): DataResponse {
		return new DataResponse([
			'participants' => $this->service->getParticipants($id)
		]);
	}

	public function create(
		array $data,
	): DataResponse {
		return new DataResponse(
			$this->service->create($data),
			Http::STATUS_CREATED
		);
	}

	public function update(
		int $id,
		array $data,
	): DataResponse {
		return new DataResponse(
			$this->service->updateEvent($id, $data)
		);
	}

	public function delete(
		int $id,
	): DataResponse {
		$this->service->deleteEvent($id);

		return new DataResponse(['deleted' => true]);
	}
}
