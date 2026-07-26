<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Service\RelationshipGraphService;
use OCA\Kinship\Service\RelationshipService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class RelationshipController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private RelationshipService $service,
		private RelationshipGraphService $graphService,
	) {
		parent::__construct(
			$appName,
			$request
		);
	}

	public function index(): DataResponse {
		return new DataResponse([
			'relationships' => $this->service->getAll()
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
	 * Stored (canonical) relationships for a person.
	 */
	public function personRelationships(
		int $personId,
	): DataResponse {
		return new DataResponse([
			'relationships' => $this->service->getForPerson($personId)
		]);
	}

	/**
	 * Derived (computed) relationships for a person.
	 */
	public function personDerived(
		int $personId,
	): DataResponse {
		return new DataResponse([
			'derived' => $this->graphService->getDerived($personId)
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
			$this->service->update($id, $data)
		);
	}

	public function delete(
		int $id,
	): DataResponse {
		$this->service->delete($id);

		return new DataResponse(['deleted' => true]);
	}
}
