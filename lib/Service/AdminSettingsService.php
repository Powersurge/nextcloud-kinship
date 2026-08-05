<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\AppInfo\Application;
use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Constants\PlaceLookupProvider;
use OCP\IAppConfig;

class AdminSettingsService {
	public const KEY_GEDCOM_IMPORT = 'gedcom_import_enabled';
	public const KEY_EXTENSIONS = 'extensions_enabled';
	public const KEY_PRIVACY_THRESHOLD = 'privacy_threshold';
	public const KEY_PLACE_LOOKUP_PROVIDER = 'place_lookup_provider';
	public const KEY_NOMINATIM_BASE_URL = 'nominatim_base_url';
	public const KEY_GOOGLE_MAPS_API_KEY = 'google_maps_api_key';
	public const KEY_DEFAULT_ROLE = 'default_role';
	public const KEY_EDITOR_CAN_DELETE = 'editor_can_delete';
	public const KEY_EDITOR_CAN_ACCEPT_CLAIMS = 'editor_can_accept_claims';

	public const DEFAULT_PRIVACY_THRESHOLD = 110;
	public const MIN_PRIVACY_THRESHOLD = 1;
	public const MAX_PRIVACY_THRESHOLD = 200;
	public const DEFAULT_PLACE_LOOKUP_PROVIDER = PlaceLookupProvider::NOMINATIM;
	public const DEFAULT_NOMINATIM_BASE_URL = 'https://nominatim.openstreetmap.org';
	public const DEFAULT_ROLE = PermissionRole::VIEWER;

	public const SETTINGS = [
		self::KEY_GEDCOM_IMPORT => [
			'type' => 'boolean',
			'responseKey' => 'gedcomImportEnabled',
			'default' => false,
		],
		self::KEY_EXTENSIONS => [
			'type' => 'boolean',
			'responseKey' => 'extensionsEnabled',
			'default' => false,
		],
		self::KEY_PRIVACY_THRESHOLD => [
			'type' => 'integer',
			'responseKey' => 'privacyThreshold',
			'default' => self::DEFAULT_PRIVACY_THRESHOLD,
		],
		self::KEY_PLACE_LOOKUP_PROVIDER => [
			'type' => 'string',
			'responseKey' => 'placeLookupProvider',
			'default' => self::DEFAULT_PLACE_LOOKUP_PROVIDER,
		],
		self::KEY_NOMINATIM_BASE_URL => [
			'type' => 'string',
			'responseKey' => 'nominatimBaseUrl',
			'default' => self::DEFAULT_NOMINATIM_BASE_URL,
		],
		self::KEY_GOOGLE_MAPS_API_KEY => [
			'type' => 'string',
			'responseKey' => 'googleMapsApiKey',
			'default' => '',
			'sensitive' => true,
		],
		self::KEY_DEFAULT_ROLE => [
			'type' => 'string',
			'responseKey' => 'defaultRole',
			'default' => self::DEFAULT_ROLE,
		],
		self::KEY_EDITOR_CAN_DELETE => [
			'type' => 'boolean',
			'responseKey' => 'editorCanDelete',
			'default' => true,
		],
		self::KEY_EDITOR_CAN_ACCEPT_CLAIMS => [
			'type' => 'boolean',
			'responseKey' => 'editorCanAcceptClaims',
			'default' => true,
		],
	];

	public function __construct(
		private IAppConfig $config,
	) {
	}

	/**
	 * @return array{
	 *   gedcomImportEnabled: bool,
	 *   extensionsEnabled: bool,
	 *   privacyThreshold: int,
	 *   placeLookupProvider: string,
	 *   nominatimBaseUrl: string,
	 *   googleMapsApiKey: string,
	 *   defaultRole: string,
	 *   editorCanDelete: bool,
	 *   editorCanAcceptClaims: bool
	 * }
	 */
	public function getAll(): array {
		return [
			'gedcomImportEnabled' => $this->isGedcomImportEnabled(),
			'extensionsEnabled' => $this->isExtensionsEnabled(),
			'privacyThreshold' => $this->getPrivacyThreshold(),
			'placeLookupProvider' => $this->getPlaceLookupProvider(),
			'nominatimBaseUrl' => $this->getNominatimBaseUrl(),
			'googleMapsApiKey' => $this->getGoogleMapsApiKey(),
			'defaultRole' => $this->getDefaultRole(),
			'editorCanDelete' => $this->isEditorCanDelete(),
			'editorCanAcceptClaims' => $this->isEditorCanAcceptClaims(),
		];
	}

	public function isGedcomImportEnabled(): bool {
		return $this->config->getValueBool(
			Application::APP_ID,
			self::KEY_GEDCOM_IMPORT,
		);
	}

	public function isExtensionsEnabled(): bool {
		return $this->config->getValueBool(
			Application::APP_ID,
			self::KEY_EXTENSIONS,
		);
	}

	public function getPrivacyThreshold(): int {
		$value = $this->config->getValueInt(
			Application::APP_ID,
			self::KEY_PRIVACY_THRESHOLD,
			self::DEFAULT_PRIVACY_THRESHOLD,
		);

		return $this->normalizePrivacyThreshold($value);
	}

	public function getPlaceLookupProvider(): string {
		$value = $this->config->getValueString(
			Application::APP_ID,
			self::KEY_PLACE_LOOKUP_PROVIDER,
			self::DEFAULT_PLACE_LOOKUP_PROVIDER,
		);

		return $this->normalizePlaceLookupProvider($value);
	}

	public function getNominatimBaseUrl(): string {
		$value = trim($this->config->getValueString(
			Application::APP_ID,
			self::KEY_NOMINATIM_BASE_URL,
			self::DEFAULT_NOMINATIM_BASE_URL,
		));

		return $value !== '' ? $value : self::DEFAULT_NOMINATIM_BASE_URL;
	}

	public function getGoogleMapsApiKey(): string {
		return $this->config->getValueString(
			Application::APP_ID,
			self::KEY_GOOGLE_MAPS_API_KEY,
			'',
		);
	}

	public function getDefaultRole(): string {
		$value = $this->config->getValueString(
			Application::APP_ID,
			self::KEY_DEFAULT_ROLE,
			self::DEFAULT_ROLE,
		);

		return $this->normalizeDefaultRole($value);
	}

	public function isEditorCanDelete(): bool {
		return $this->config->getValueBool(
			Application::APP_ID,
			self::KEY_EDITOR_CAN_DELETE,
			true,
		);
	}

	public function isEditorCanAcceptClaims(): bool {
		return $this->config->getValueBool(
			Application::APP_ID,
			self::KEY_EDITOR_CAN_ACCEPT_CLAIMS,
			true,
		);
	}

	/**
	 * @param array{
	 *   gedcomImportEnabled?: bool,
	 *   extensionsEnabled?: bool,
	 *   privacyThreshold?: int,
	 *   placeLookupProvider?: string,
	 *   nominatimBaseUrl?: string,
	 *   googleMapsApiKey?: string,
	 *   defaultRole?: string,
	 *   editorCanDelete?: bool,
	 *   editorCanAcceptClaims?: bool
	 * } $settings
	 */
	public function update(array $settings): void {
		$appId = Application::APP_ID;

		foreach (self::SETTINGS as $configKey => $settingConfig) {
			$responseKey = $settingConfig['responseKey'];

			if (!array_key_exists($responseKey, $settings)) {
				continue;
			}

			$value = $settings[$responseKey];

			if ($configKey === self::KEY_PRIVACY_THRESHOLD) {
				$value = $this->normalizePrivacyThreshold((int)$value);
			}
			if ($configKey === self::KEY_PLACE_LOOKUP_PROVIDER) {
				$value = $this->normalizePlaceLookupProvider((string)$value);
			}
			if ($configKey === self::KEY_NOMINATIM_BASE_URL) {
				$value = trim((string)$value);
				if ($value === '') {
					$value = self::DEFAULT_NOMINATIM_BASE_URL;
				}
			}
			if ($configKey === self::KEY_GOOGLE_MAPS_API_KEY) {
				$value = trim((string)$value);
			}
			if ($configKey === self::KEY_DEFAULT_ROLE) {
				$value = $this->normalizeDefaultRole((string)$value);
			}

			$sensitive = !empty($settingConfig['sensitive']);

			switch ($settingConfig['type']) {
				case 'boolean':
					$this->config->setValueBool($appId, $configKey, (bool)$value);
					break;
				case 'integer':
					$this->config->setValueInt($appId, $configKey, (int)$value);
					break;
				case 'string':
					$this->config->setValueString($appId, $configKey, (string)$value, false, $sensitive);
					break;
			}
		}
	}

	private function normalizePrivacyThreshold(int $value): int {
		return max(
			self::MIN_PRIVACY_THRESHOLD,
			min(self::MAX_PRIVACY_THRESHOLD, $value),
		);
	}

	private function normalizePlaceLookupProvider(string $value): string {
		if (!in_array($value, PlaceLookupProvider::ALL, true)) {
			return self::DEFAULT_PLACE_LOOKUP_PROVIDER;
		}

		return $value;
	}

	private function normalizeDefaultRole(string $value): string {
		if (!in_array($value, PermissionRole::DEFAULT_ROLE_OPTIONS, true)) {
			return self::DEFAULT_ROLE;
		}

		return $value;
	}
}
