<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\PlaceLookupProvider;
use OCA\Kinship\Db\Place;
use OCP\Http\Client\IClientService;
use Psr\Log\LoggerInterface;

/**
 * Merges local place matches with optional Nominatim / Google geocoding results.
 */
class PlaceLookupService {
	public function __construct(
		private PlaceService $placeService,
		private AdminSettingsService $adminSettings,
		private IClientService $clientService,
		private LoggerInterface $logger,
	) {
	}

	/**
	 * @return list<array<string, mixed>>
	 */
	public function lookup(string $query, ?array $selected = []): array {
		$query = trim($query);
		$results = [];
		$seenProviderKeys = [];

		$localPlaces = $this->placeService->getAll([
			'searchQuery' => $query !== '' ? $query : null,
			'selected' => $query === '' ? ($selected ?? []) : [],
			'limit' => 20,
		]);

		foreach ($localPlaces as $place) {
			$item = $this->fromLocalPlace($place);
			$results[] = $item;
			$key = $this->providerKey($item['provider'], $item['providerPlaceId']);
			if ($key !== null) {
				$seenProviderKeys[$key] = true;
			}
		}

		if ($query === '') {
			return $results;
		}

		foreach ($this->searchExternal($query) as $external) {
			$key = $this->providerKey($external['provider'], $external['providerPlaceId']);
			if ($key !== null && isset($seenProviderKeys[$key])) {
				continue;
			}
			$results[] = $external;
			if ($key !== null) {
				$seenProviderKeys[$key] = true;
			}
		}

		return $results;
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function reverse(string $latitude, string $longitude): ?array {
		$provider = $this->adminSettings->getPlaceLookupProvider();

		return match ($provider) {
			PlaceLookupProvider::NOMINATIM => $this->reverseNominatim($latitude, $longitude),
			PlaceLookupProvider::GOOGLE => $this->reverseGoogle($latitude, $longitude),
			default => [
				'id' => null,
				'name' => sprintf('%s, %s', $latitude, $longitude),
				'fullName' => sprintf('%s, %s', $latitude, $longitude),
				'latitude' => $latitude,
				'longitude' => $longitude,
				'provider' => null,
				'providerPlaceId' => null,
				'source' => 'manual',
			],
		};
	}

	public function getConfiguredProvider(): string {
		return $this->adminSettings->getPlaceLookupProvider();
	}

	/**
	 * @return list<array<string, mixed>>
	 */
	private function searchExternal(string $query): array {
		$provider = $this->adminSettings->getPlaceLookupProvider();

		return match ($provider) {
			PlaceLookupProvider::NOMINATIM => $this->searchNominatim($query),
			PlaceLookupProvider::GOOGLE => $this->searchGoogle($query),
			default => [],
		};
	}

	/**
	 * @return list<array<string, mixed>>
	 */
	private function searchNominatim(string $query): array {
		$baseUrl = rtrim($this->adminSettings->getNominatimBaseUrl(), '/');
		$url = $baseUrl . '/search';

		try {
			$response = $this->clientService->newClient()->get($url, [
				'query' => [
					'q' => $query,
					'format' => 'json',
					'addressdetails' => 1,
					'limit' => 8,
				],
				'headers' => [
					'Accept' => 'application/json',
					'User-Agent' => 'Kinship-Nextcloud-App/1.0',
				],
				'timeout' => 10,
			]);

			$data = json_decode($response->getBody(), true);
			if (!is_array($data)) {
				return [];
			}

			$results = [];
			foreach ($data as $row) {
				if (!is_array($row)) {
					continue;
				}
				$mapped = $this->fromNominatim($row);
				if ($mapped !== null) {
					$results[] = $mapped;
				}
			}

			return $results;
		} catch (\Throwable $e) {
			$this->logger->warning('Nominatim search failed: {message}', [
				'message' => $e->getMessage(),
			]);
			return [];
		}
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function reverseNominatim(string $latitude, string $longitude): ?array {
		$baseUrl = rtrim($this->adminSettings->getNominatimBaseUrl(), '/');
		$url = $baseUrl . '/reverse';

		try {
			$response = $this->clientService->newClient()->get($url, [
				'query' => [
					'lat' => $latitude,
					'lon' => $longitude,
					'format' => 'json',
					'addressdetails' => 1,
				],
				'headers' => [
					'Accept' => 'application/json',
					'User-Agent' => 'Kinship-Nextcloud-App/1.0',
				],
				'timeout' => 10,
			]);

			$data = json_decode($response->getBody(), true);
			if (!is_array($data) || isset($data['error'])) {
				return null;
			}

			return $this->fromNominatim($data);
		} catch (\Throwable $e) {
			$this->logger->warning('Nominatim reverse geocode failed: {message}', [
				'message' => $e->getMessage(),
			]);
			return null;
		}
	}

	/**
	 * @return list<array<string, mixed>>
	 */
	private function searchGoogle(string $query): array {
		$apiKey = $this->adminSettings->getGoogleMapsApiKey();
		if ($apiKey === '') {
			return [];
		}

		try {
			$response = $this->clientService->newClient()->get(
				'https://maps.googleapis.com/maps/api/geocode/json',
				[
					'query' => [
						'address' => $query,
						'key' => $apiKey,
					],
					'timeout' => 10,
				],
			);

			$data = json_decode($response->getBody(), true);
			if (!is_array($data) || ($data['status'] ?? '') !== 'OK' || !is_array($data['results'] ?? null)) {
				return [];
			}

			$results = [];
			foreach ($data['results'] as $row) {
				if (!is_array($row)) {
					continue;
				}
				$mapped = $this->fromGoogle($row);
				if ($mapped !== null) {
					$results[] = $mapped;
				}
			}

			return $results;
		} catch (\Throwable $e) {
			$this->logger->warning('Google Maps search failed: {message}', [
				'message' => $e->getMessage(),
			]);
			return [];
		}
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function reverseGoogle(string $latitude, string $longitude): ?array {
		$apiKey = $this->adminSettings->getGoogleMapsApiKey();
		if ($apiKey === '') {
			return null;
		}

		try {
			$response = $this->clientService->newClient()->get(
				'https://maps.googleapis.com/maps/api/geocode/json',
				[
					'query' => [
						'latlng' => $latitude . ',' . $longitude,
						'key' => $apiKey,
					],
					'timeout' => 10,
				],
			);

			$data = json_decode($response->getBody(), true);
			if (!is_array($data) || ($data['status'] ?? '') !== 'OK' || empty($data['results'][0])) {
				return null;
			}

			return $this->fromGoogle($data['results'][0]);
		} catch (\Throwable $e) {
			$this->logger->warning('Google Maps reverse geocode failed: {message}', [
				'message' => $e->getMessage(),
			]);
			return null;
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	private function fromLocalPlace(Place $place): array {
		return [
			'id' => $place->getId(),
			'name' => $place->getName(),
			'fullName' => $place->getFullName(),
			'latitude' => $place->getLatitude(),
			'longitude' => $place->getLongitude(),
			'provider' => $place->getProvider(),
			'providerPlaceId' => $place->getProviderPlaceId(),
			'source' => 'local',
		];
	}

	/**
	 * @param array<string, mixed> $row
	 * @return array<string, mixed>|null
	 */
	private function fromNominatim(array $row): ?array {
		$displayName = isset($row['display_name']) && is_string($row['display_name'])
			? $row['display_name']
			: null;
		if ($displayName === null || $displayName === '') {
			return null;
		}

		$name = $displayName;
		if (isset($row['address']) && is_array($row['address'])) {
			$address = $row['address'];
			foreach (['city', 'town', 'village', 'hamlet', 'municipality', 'county', 'state', 'country'] as $key) {
				if (!empty($address[$key]) && is_string($address[$key])) {
					$name = $address[$key];
					break;
				}
			}
		} elseif (isset($row['name']) && is_string($row['name']) && $row['name'] !== '') {
			$name = $row['name'];
		}

		$placeId = isset($row['place_id']) ? (string)$row['place_id'] : null;
		if ($placeId === null && isset($row['osm_type'], $row['osm_id'])) {
			$placeId = $row['osm_type'] . ':' . $row['osm_id'];
		}

		return [
			'id' => null,
			'name' => $name,
			'fullName' => $displayName,
			'latitude' => isset($row['lat']) ? (string)$row['lat'] : null,
			'longitude' => isset($row['lon']) ? (string)$row['lon'] : null,
			'provider' => PlaceLookupProvider::NOMINATIM,
			'providerPlaceId' => $placeId,
			'source' => 'external',
		];
	}

	/**
	 * @param array<string, mixed> $row
	 * @return array<string, mixed>|null
	 */
	private function fromGoogle(array $row): ?array {
		$fullName = isset($row['formatted_address']) && is_string($row['formatted_address'])
			? $row['formatted_address']
			: null;
		if ($fullName === null || $fullName === '') {
			return null;
		}

		$name = $fullName;
		if (!empty($row['address_components'][0]['long_name'])
			&& is_string($row['address_components'][0]['long_name'])) {
			$name = $row['address_components'][0]['long_name'];
		}

		$location = $row['geometry']['location'] ?? null;

		return [
			'id' => null,
			'name' => $name,
			'fullName' => $fullName,
			'latitude' => is_array($location) && isset($location['lat']) ? (string)$location['lat'] : null,
			'longitude' => is_array($location) && isset($location['lng']) ? (string)$location['lng'] : null,
			'provider' => PlaceLookupProvider::GOOGLE,
			'providerPlaceId' => isset($row['place_id']) && is_string($row['place_id'])
				? $row['place_id']
				: null,
			'source' => 'external',
		];
	}

	private function providerKey(?string $provider, ?string $providerPlaceId): ?string {
		if ($provider === null || $provider === '' || $providerPlaceId === null || $providerPlaceId === '') {
			return null;
		}

		return $provider . ':' . $providerPlaceId;
	}
}
