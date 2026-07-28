<?php

declare(strict_types=1);

namespace OCA\Kinship\Cron;

use OCA\Kinship\Service\LivingStatusService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;

/**
 * Daily reconciliation of living_status against the privacy threshold.
 */
class PrivacyThresholdJob extends TimedJob {
	public function __construct(
		ITimeFactory $timeFactory,
		private LivingStatusService $livingStatusService,
	) {
		parent::__construct($timeFactory);

		$this->setInterval(24 * 60 * 60);
	}

	protected function run($argument): void {
		$this->livingStatusService->applyPrivacyThresholdToAll();
	}
}
