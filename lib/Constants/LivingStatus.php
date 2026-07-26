<?php

declare(strict_types=1);

namespace OCA\Kinship\Constants;

/**
 * Cached living status of a person (kinship_persons.living_status).
 *
 * v0.1 stores an explicit value; the privacy-threshold driven auto-detection
 * (living / deceased / unknown when age exceeds the threshold) arrives in v0.2.
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
