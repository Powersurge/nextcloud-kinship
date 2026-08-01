<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Db\Place;
use OCA\Kinship\Db\PlaceMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

/**
 * Places gazetteer. Owner-only editing is enforced in v0.2; v0.1 records the
 * owner and performs CRUD.
 */
class PlaceService {
	public function __construct(
		private PlaceMapper $mapper,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	/**
	 * @return Place[]
	 */
	public function getAll(array $queryParams = []): array {
		return $this->mapper->findAll($queryParams);
	}

	public function get(int $id): Place {
		return $this->mapper->find($id);
	}

	public function create(array $data): Place {
		$now = $this->timeFactory->getTime();
		$uid = $this->userSession->getUser()?->getUID();

		$place = new Place();
		$place->setName($data['name'] ?? '');
		$place->setFullName($data['fullName'] ?? null);
		$place->setParentId(isset($data['parentId']) ? (int)$data['parentId'] : null);
		$place->setLatitude($data['latitude'] ?? null);
		$place->setLongitude($data['longitude'] ?? null);
		$place->setType($data['type'] ?? null);
		$place->setNotes($data['notes'] ?? null);
		$place->setProvider($data['provider'] ?? null);
		$place->setProviderPlaceId($data['providerPlaceId'] ?? null);
		$place->setOwnerUid($uid);
		$place->setCreatedBy($uid);
		$place->setCreatedAt($now);
		$place->setUpdatedAt($now);

		return $this->mapper->insert($place);
	}

	public function update(int $id, array $data): Place {
		$place = $this->mapper->find($id);

		if (array_key_exists('name', $data)) {
			$place->setName($data['name']);
		}
		if (array_key_exists('fullName', $data)) {
			$place->setFullName($data['fullName']);
		}
		if (array_key_exists('parentId', $data)) {
			$place->setParentId($data['parentId'] !== null ? (int)$data['parentId'] : null);
		}
		if (array_key_exists('latitude', $data)) {
			$place->setLatitude($data['latitude']);
		}
		if (array_key_exists('longitude', $data)) {
			$place->setLongitude($data['longitude']);
		}
		if (array_key_exists('type', $data)) {
			$place->setType($data['type']);
		}
		if (array_key_exists('notes', $data)) {
			$place->setNotes($data['notes']);
		}
		if (array_key_exists('provider', $data)) {
			$place->setProvider($data['provider']);
		}
		if (array_key_exists('providerPlaceId', $data)) {
			$place->setProviderPlaceId($data['providerPlaceId']);
		}

		$place->setUpdatedAt($this->timeFactory->getTime());

		return $this->mapper->update($place);
	}

	/**
	 * Find an existing place by provider provenance, or create one from lookup data.
	 */
	public function resolve(array $data): Place {
		if (isset($data['id']) && is_numeric($data['id'])) {
			return $this->mapper->find((int)$data['id']);
		}

		$provider = $data['provider'] ?? null;
		$providerPlaceId = $data['providerPlaceId'] ?? null;

		if (!empty($provider) && !empty($providerPlaceId)) {
			$existing = $this->mapper->findByProvider($provider, $providerPlaceId);

			if ($existing !== null) {
				return $existing;
			}
		}

		return $this->create($data);
	}

	public function delete(int $id): void {
		$this->mapper->delete($this->mapper->find($id));
	}
}
