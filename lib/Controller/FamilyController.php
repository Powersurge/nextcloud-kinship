<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Db\Family;
use OCA\Kinship\Service\FamilyService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class FamilyController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private FamilyService $service,
	) {
		parent::__construct(
			$appName,
			$request
		);
	}

	/**
	 * Return all families.
	 */
	public function index(): DataResponse {
		return new DataResponse([
			'families' => $this->service->getAll()
		]);
	}

	/**
	 * Return a single family.
	 */
	public function show(
		int $id,
	): DataResponse {
		return new DataResponse(
			$this->service->get($id)
		);
	}

	/**
	 * Create family.
	 */
	public function create(
		array $data,
	): DataResponse {

		$family = new Family();

		$family->setName(
			$data['name'] ?? ''
		);

		$family->setDescription(
			$data['description'] ?? null
		);

		return new DataResponse(
			$this->service->create(
				$family
			)
		);
	}

	/**
	 * Update family.
	 */
	public function update(
		int $id,
		array $data,
	): DataResponse {

		return new DataResponse(
			$this->service->updateFamily(
				$id,
				$data
			)
		);
	}

	/**
	 * Delete family.
	 */
	public function delete(
		int $id,
	): DataResponse {

		$this->service->deleteFamily(
			$id
		);

		return new DataResponse([
			'deleted' => true
		]);
	}
}
