<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\AppInfo\Application;
use OCP\IAppConfig;

class AdminSettingsService {
	public const KEY_GEDCOM_IMPORT = 'gedcom_import_enabled';
	public const KEY_EXTENSIONS = 'extensions_enabled';
	public const KEY_PRIVACY_THRESHOLD = 'privacy_threshold';
	public const DEFAULT_PRIVACY_THRESHOLD = 110;
	public const MIN_PRIVACY_THRESHOLD = 1;
	public const MAX_PRIVACY_THRESHOLD = 200;

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
		]
	];

	public function __construct(
		private IAppConfig $config,
	) {
	}

	/**
	 * @return array{gedcomImportEnabled: bool, extensionsEnabled: bool, privacyThreshold: int}
	 */
	public function getAll(): array {
		return [
			'gedcomImportEnabled' => $this->isGedcomImportEnabled(),
			'extensionsEnabled' => $this->isExtensionsEnabled(),
			'privacyThreshold' => $this->getPrivacyThreshold(),
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

	/**
	 * @param array{gedcomImportEnabled?: bool, extensionsEnabled?: bool, privacyThreshold?: int} $settings
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
				$value = $this->normalizePrivacyThreshold($value);
			}

			switch ($settingConfig['type']) {
				case 'boolean':
					$this->config->setValueBool($appId, $configKey, $value);
					break;
				case 'integer':
					$this->config->setValueInt($appId, $configKey, $value);
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
}
