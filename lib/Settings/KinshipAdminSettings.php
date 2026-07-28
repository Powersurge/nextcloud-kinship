<?php

declare(strict_types=1);

namespace OCA\Kinship\Settings;

use OCA\Kinship\AppInfo\Application;
use OCA\Kinship\Service\AdminSettingsService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\Settings\ISettings;
use OCP\Util;

class KinshipAdminSettings implements ISettings {
	public function __construct(
		private AdminSettingsService $adminSettingsService,
		private IInitialState $initialState,
	) {
	}

	public function getForm(): TemplateResponse {
		$this->initialState->provideInitialState(
			'admin-settings',
			$this->adminSettingsService->getAll(),
		);

		Util::addScript(Application::APP_ID, Application::APP_ID . '-admin');
		Util::addStyle(Application::APP_ID, Application::APP_ID . '-admin');

		return new TemplateResponse('kinship', 'admin');
	}

	public function getSection(): string {
		return 'kinship';
	}

	public function getPriority(): int {
		return 80;
	}
}
