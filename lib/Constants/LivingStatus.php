<?php

declare(strict_types=1);

namespace OCA\Kinship\Constants;

/**
 * Cached living status of a person (kinship_persons.living_status).
 *
 * Derived automatically from death events and the admin privacy threshold
 * (see LivingStatusService).
 */
final class LivingStatus {
	public const LIVING = 'living';
	public const DECEASED = 'deceased';
	public const UNKNOWN = 'unknown';

	public const ALL = [
		self::LIVING,
		self::DECEASED,
		self::UNKNOWN,
	];

	public static function isValid(string $status): bool {
		return in_array($status, self::ALL, true);
	}
}
