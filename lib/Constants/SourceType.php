<?php

declare(strict_types=1);

namespace OCA\Kinship\Constants;

/**
 * Bibliographic source categories. Stored as free strings; UI constrains to these.
 */
final class SourceType {
	public const BOOK = 'book';
	public const CENSUS = 'census';
	public const VITAL = 'vital';
	public const NEWSPAPER = 'newspaper';
	public const WEBSITE = 'website';
	public const CERTIFICATE = 'certificate';
	public const LETTER = 'letter';
	public const OTHER = 'other';

	public const ALL = [
		self::BOOK,
		self::CENSUS,
		self::VITAL,
		self::NEWSPAPER,
		self::WEBSITE,
		self::CERTIFICATE,
		self::LETTER,
		self::OTHER,
	];

	public static function isValid(string $type): bool {
		return in_array($type, self::ALL, true);
	}
}
