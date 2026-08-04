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

	/** Subjects that may receive citations in v0.2. */
	public const CITABLE = [
		self::PERSON,
		self::EVENT,
		self::FACT,
		self::RELATIONSHIP,
		self::FAMILY,
	];

	/** Genealogy objects that may own albums. */
	public const ALBUM_OWNERS = [
		self::PERSON,
		self::FAMILY,
		self::EVENT,
	];

	public static function isValid(string $type): bool {
		return in_array($type, self::ALL, true);
	}

	public static function isCitable(string $type): bool {
		return in_array($type, self::CITABLE, true);
	}

	public static function isAlbumOwner(string $type): bool {
		return in_array($type, self::ALBUM_OWNERS, true);
	}
}
