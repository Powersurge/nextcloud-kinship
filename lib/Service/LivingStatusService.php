<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use DateTimeImmutable;
use OCA\Kinship\Constants\EventType;
use OCA\Kinship\Constants\LivingStatus;
use OCA\Kinship\Db\DateValue;
use OCA\Kinship\Db\Event;
use OCA\Kinship\Db\EventMapper;
use OCA\Kinship\Db\PersonMapper;
use OCP\AppFramework\Utility\ITimeFactory;

/**
 * Derives cached living_status from life events and the privacy threshold.
 */
class LivingStatusService {
	public function __construct(
		private PersonMapper $personMapper,
		private EventMapper $eventMapper,
		private AdminSettingsService $adminSettingsService,
		private ITimeFactory $timeFactory,
	) {
	}

	/**
	 * Reconcile living_status for every person (cron entry point).
	 *
	 * @return int Number of people updated
	 */
	public function applyPrivacyThresholdToAll(): int {
		$updated = 0;

		foreach ($this->personMapper->findAll() as $person) {
			if ($this->syncPersonLivingStatus($person->getId())) {
				$updated++;
			}
		}

		return $updated;
	}

	/**
	 * Reconcile living_status for one person from their life events.
	 *
	 * @return bool Whether the stored status changed
	 */
	public function syncPersonLivingStatus(int $personId): bool {
		$person = $this->personMapper->findOrNull($personId);
		if ($person === null) {
			return false;
		}

		$lifeEvents = $this->eventMapper->findLifeEventsByPersons([$personId])[$personId] ?? [];
		$targetStatus = $this->resolveLivingStatus(
			$lifeEvents[EventType::BIRTH] ?? null,
			$lifeEvents[EventType::DEATH] ?? null,
			$this->adminSettingsService->getPrivacyThreshold(),
		);

		if ($targetStatus === null || $person->getLivingStatus() === $targetStatus) {
			return false;
		}

		$person->setLivingStatus($targetStatus);
		$person->setUpdatedAt($this->timeFactory->getTime());
		$this->personMapper->update($person);

		return true;
	}

	/**
	 * Death always wins; otherwise age beyond the privacy threshold means unknown.
	 * Returns null when no automatic status applies.
	 */
	public function resolveLivingStatus(
		?Event $birthEvent,
		?Event $deathEvent,
		int $privacyThresholdYears,
	): ?string {
		if ($deathEvent !== null) {
			return LivingStatus::DECEASED;
		}

		if ($birthEvent !== null && $this->birthExceedsPrivacyThreshold(
			$birthEvent->getDate(),
			$privacyThresholdYears,
		)) {
			return LivingStatus::UNKNOWN;
		}

		return null;
	}

	public function birthExceedsPrivacyThreshold(
		DateValue $birthDate,
		int $privacyThresholdYears,
	): bool {
		if ($birthDate->isEmpty() || $birthDate->start_year === null) {
			return false;
		}

		$birth = $this->getBirthReferenceDate($birthDate);
		if ($birth === null) {
			return false;
		}

		$cutoff = new DateTimeImmutable('@' . $this->timeFactory->getTime())
			->setTimezone($birth->getTimezone())
			->modify('-' . $privacyThresholdYears . ' years');

		return $birth < $cutoff;
	}

	private function getBirthReferenceDate(DateValue $date): ?DateTimeImmutable {
		if ($date->start_year === null) {
			return null;
		}

		$month = $date->start_month ?? 1;
		$day = $date->start_day ?? 1;

		$formatted = sprintf('%04d-%02d-%02d', $date->start_year, $month, $day);

		$birth = DateTimeImmutable::createFromFormat('!Y-m-d', $formatted);
		if ($birth === false) {
			return null;
		}

		return $birth;
	}
}
