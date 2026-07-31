<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\EventType;
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
		int $limit = 10,
		int $offset = 0,
		?string $type = null,
	): DataResponse {
		if ($type !== null && !EventType::isValid($type)) {
			$limit = max(1, min(50, $limit));
			$offset = max(0, $offset);

			return new DataResponse([
				'events' => [],
				'total' => 0,
				'limit' => $limit,
				'offset' => $offset,
			]);
		}

		return new DataResponse(
			$this->service->getForPersonPaginated($personId, $limit, $offset, $type)
		);
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
