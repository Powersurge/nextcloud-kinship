<?php

declare(strict_types=1);

namespace OCA\Kinship\Constants;

/**
 * Polymorphic subject discriminator used by facts, citations, claims and media
 * to point at any genealogy object.
 */
final class SubjectType {
	public const PERSON = 'person';
	public const EVENT = 'event';
	public const FACT = 'fact';
	public const RELATIONSHIP = 'relationship';
	public const FAMILY = 'family';
	public const SOURCE = 'source';
	public const PLACE = 'place';
	public const CLAIM = 'claim';

	public const ALL = [
		self::PERSON,
		self::EVENT,
		self::FACT,
		self::RELATIONSHIP,
		self::FAMILY,
		self::SOURCE,
		self::PLACE,
		self::CLAIM,
	];

	public static function isValid(string $type): bool {
		return in_array($type, self::ALL, true);
	}
}
