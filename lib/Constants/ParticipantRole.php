<?php

declare(strict_types=1);

namespace OCA\Kinship\Constants;

/**
 * Role a person plays in an event (kinship_participants.role).
 *
 * SUBJECT is the principal (e.g. the person being born for a birth event, the
 * graduate for an education event). Marriages have two SPOUSE participants.
 */
final class ParticipantRole {
	public const SUBJECT = 'subject';
	public const SPOUSE = 'spouse';
	public const PARENT = 'parent';
	public const CHILD = 'child';
	public const WITNESS = 'witness';
	public const OFFICIANT = 'officiant';
	public const OTHER = 'other';

	public const ALL = [
		self::SUBJECT,
		self::SPOUSE,
		self::PARENT,
		self::CHILD,
		self::WITNESS,
		self::OFFICIANT,
		self::OTHER,
	];

	public static function isValid(string $role): bool {
		return in_array($role, self::ALL, true);
	}
}
