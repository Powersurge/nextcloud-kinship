<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Service\ParticipantService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ParticipantController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private ParticipantService $service,
	) {
		parent::__construct($appName, $request);
	}

	public function personParticipations(int $personId): DataResponse {
		return new DataResponse(['participants' => $this->service->getForPerson($personId)]);
	}

	public function create(array $data): DataResponse {
		return new DataResponse($this->service->create($data), Http::STATUS_CREATED);
	}

	public function delete(int $id): DataResponse {
		$this->service->delete($id);

		return new DataResponse(['deleted' => true]);
	}
}
