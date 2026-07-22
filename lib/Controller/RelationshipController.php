<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Service\RelationshipService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class RelationshipController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private RelationshipService $service,
	) {
		parent::__construct(
			$appName,
			$request
		);
	}

	/**
	 * Return all relationships.
	 */
	public function index(): DataResponse {
		return new DataResponse([
			'relationships' => $this->service->getAll()
		]);
	}

	/**
	 * Return one relationship.
	 */
	public function show(
		int $id,
	): DataResponse {
		return new DataResponse(
			$this->service->get($id)
		);
	}

	/**
	 * Return relationships for a person.
	 */
	public function personRelationships(
		int $personId,
	): DataResponse {
		return new DataResponse([
			'relationships'
				=> $this->service->getForPerson($personId)
		]);
	}
}
