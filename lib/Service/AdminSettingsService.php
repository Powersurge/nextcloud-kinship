<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\AppInfo\Application;
use OCP\IAppConfig;

class AdminSettingsService {
	public const KEY_GEDCOM_IMPORT = 'gedcom_import_enabled';
	public const KEY_EXTENSIONS = 'extensions_enabled';

	public function __construct(
		private IAppConfig $config,
	) {
	}

	/**
	 * @return array{gedcomImportEnabled: bool, extensionsEnabled: bool}
	 */
	public function getAll(): array {
		return [
			'gedcomImportEnabled' => $this->isGedcomImportEnabled(),
			'extensionsEnabled' => $this->isExtensionsEnabled(),
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

	/**
	 * @param array{gedcomImportEnabled?: bool, extensionsEnabled?: bool} $settings
	 */
	public function update(array $settings): void {
		if (array_key_exists('gedcomImportEnabled', $settings)) {
			$this->config->setValueBool(
				Application::APP_ID,
				self::KEY_GEDCOM_IMPORT,
				$settings['gedcomImportEnabled'],
			);
		}

		if (array_key_exists('extensionsEnabled', $settings)) {
			$this->config->setValueBool(
				Application::APP_ID,
				self::KEY_EXTENSIONS,
				$settings['extensionsEnabled'],
			);
		}
	}
}
