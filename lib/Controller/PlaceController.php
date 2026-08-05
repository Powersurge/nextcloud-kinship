<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Service\PermissionService;
use OCA\Kinship\Service\PlaceLookupService;
use OCA\Kinship\Service\PlaceService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class PlaceController extends ApiController {
	use ChecksPermissions;

	public function __construct(
		string $appName,
		IRequest $request,
		private PlaceService $service,
		private PlaceLookupService $lookupService,
		private PermissionService $permissions,
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	public function index(
		?string $searchQuery = null,
		?array $selected = [],
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

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
	#[NoAdminRequired]
	public function lookup(
		?string $searchQuery = null,
		?array $selected = [],
	): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

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
	#[NoAdminRequired]
	public function reverse(string $latitude, string $longitude): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

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
	#[NoAdminRequired]
	public function resolve(array $data): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_MANAGE_PLACES)) {
			return $denied;
		}

		return new DataResponse($this->service->resolve($data));
	}

	#[NoAdminRequired]
	public function show(int $id): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_VIEW)) {
			return $denied;
		}

		return new DataResponse($this->service->get($id));
	}

	/**
	 * @param array<string, mixed> $data
	 */
	#[NoAdminRequired]
	public function create(array $data): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_MANAGE_PLACES)) {
			return $denied;
		}

		return new DataResponse($this->service->create($data), Http::STATUS_CREATED);
	}

	/**
	 * @param array<string, mixed> $data
	 */
	#[NoAdminRequired]
	public function update(int $id, array $data): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_MANAGE_PLACES)) {
			return $denied;
		}

		return new DataResponse($this->service->update($id, $data));
	}

	#[NoAdminRequired]
	public function delete(int $id): DataResponse {
		if ($denied = $this->denyUnlessCan($this->permissions, PermissionRole::ACTION_MANAGE_PLACES)) {
			return $denied;
		}

		$this->service->delete($id);

		return new DataResponse(['deleted' => true]);
	}
}
