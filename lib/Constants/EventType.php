<?php

declare(strict_types=1);

namespace OCA\Kinship\Constants;

/**
 * Hard-coded core event types.
 *
 * When an event's type is CUSTOM its label comes from the kinship_custom_events
 * table (referenced via Event::customEventId).
 */
final class EventType {
	public const BIRTH = 'birth';
	public const DEATH = 'death';
	public const MARRIAGE = 'marriage';
	public const EDUCATION = 'education';
	public const MILITARY = 'military';
	public const CUSTOM = 'custom';

	public const ALL = [
		self::BIRTH,
		self::DEATH,
		self::MARRIAGE,
		self::EDUCATION,
		self::MILITARY,
		self::CUSTOM,
	];

	public static function isValid(string $type): bool {
		return in_array($type, self::ALL, true);
	}
}
