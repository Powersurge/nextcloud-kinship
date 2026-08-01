<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Service\PlaceLookupService;
use OCA\Kinship\Service\PlaceService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class PlaceController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private PlaceService $service,
		private PlaceLookupService $lookupService,
	) {
		parent::__construct($appName, $request);
	}

	public function index(
		?string $searchQuery = null,
		?array $selected = [],
	): DataResponse {
		return new DataResponse([
			'places' => $this->service->getAll([
				'searchQuery' => $searchQuery,
				'selected' => $selected,
			]),
		]);
	}

	/**
	 * Search local places plus the configured external geocoder.
	 */
	public function lookup(
		?string $searchQuery = null,
		?array $selected = [],
	): DataResponse {
		return new DataResponse([
			'provider' => $this->lookupService->getConfiguredProvider(),
			'results' => $this->lookupService->lookup(
				$searchQuery ?? '',
				$selected ?? [],
			),
		]);
	}

	/**
	 * Reverse-geocode a map click via the configured provider.
	 */
	public function reverse(string $latitude, string $longitude): DataResponse {
		$result = $this->lookupService->reverse($latitude, $longitude);

		if ($result === null) {
			return new DataResponse(
				['error' => 'Could not reverse geocode coordinates'],
				Http::STATUS_BAD_GATEWAY,
			);
		}

		return new DataResponse(['result' => $result]);
	}

	/**
	 * Persist a lookup / map-pick result (find-or-create by provider id).
	 *
	 * @param array<string, mixed> $data
	 */
	public function resolve(array $data): DataResponse {
		return new DataResponse($this->service->resolve($data));
	}

	public function show(int $id): DataResponse {
		return new DataResponse($this->service->get($id));
	}

	/**
	 * @param array<string, mixed> $data
	 */
	public function create(array $data): DataResponse {
		return new DataResponse($this->service->create($data), Http::STATUS_CREATED);
	}

	/**
	 * @param array<string, mixed> $data
	 */
	public function update(int $id, array $data): DataResponse {
		return new DataResponse($this->service->update($id, $data));
	}

	public function delete(int $id): DataResponse {
		$this->service->delete($id);

		return new DataResponse(['deleted' => true]);
	}
}
