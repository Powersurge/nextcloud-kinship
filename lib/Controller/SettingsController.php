<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Service\AdminSettingsService;
use OCA\Kinship\Service\LivingStatusService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class SettingsController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private AdminSettingsService $adminSettingsService,
		private LivingStatusService $livingStatusService,
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
	 * @param array<string, mixed> $settings
	 */
	#[AuthorizedAdminSetting(settings: 'OCA\Kinship\Settings\KinshipAdminSettings')]
	public function update(array $settings): DataResponse {
		$previousThreshold = $this->adminSettingsService->getPrivacyThreshold();
		$this->adminSettingsService->update($settings);

		if (
			array_key_exists('privacyThreshold', $settings)
			&& (int)$settings['privacyThreshold'] !== $previousThreshold
		) {
			$this->livingStatusService->applyPrivacyThresholdToAll();
		}

		return new DataResponse($this->adminSettingsService->getAll());
	}
}
