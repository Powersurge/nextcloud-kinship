<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Service\AdminSettingsService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class SettingsController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private AdminSettingsService $adminSettingsService,
	) {
		parent::__construct(
			$appName,
			$request,
		);
	}

	#[AdminRequired]
	public function index(): DataResponse {
		return new DataResponse($this->adminSettingsService->getAll());
	}

	/**
	 * @param array{gedcomImportEnabled?: bool, extensionsEnabled?: bool} $settings
	 */
	#[AdminRequired]
	#[AuthorizedAdminSetting(settings: 'OCA\Kinship\Settings\KinshipAdminSettings')]
	public function update(array $settings): DataResponse {
		$this->adminSettingsService->update($settings);

		return new DataResponse($this->adminSettingsService->getAll());
	}
}
