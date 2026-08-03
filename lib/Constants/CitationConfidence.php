<?php

declare(strict_types=1);

namespace OCA\Kinship\Constants;

/**
 * Optional evidence quality on a citation (GEDCOM QUAY-ish).
 * Stored but not weighted until v0.3 evidence workflows.
 */
final class CitationConfidence {
	public const PRIMARY = 'primary';
	public const SECONDARY = 'secondary';
	public const QUESTIONABLE = 'questionable';
	public const UNRELIABLE = 'unreliable';

	public const ALL = [
		self::PRIMARY,
		self::SECONDARY,
		self::QUESTIONABLE,
		self::UNRELIABLE,
	];

	/**
	 * Map GEDCOM QUAY (0–3) to Kinship confidence values.
	 */
	public static function fromQuay(mixed $quay): ?string {
		if ($quay === null || $quay === '') {
			return null;
		}

		return match ((string)$quay) {
			'3' => self::PRIMARY,
			'2' => self::SECONDARY,
			'1' => self::QUESTIONABLE,
			'0' => self::UNRELIABLE,
			default => null,
		};
	}

	public static function isValid(string $confidence): bool {
		return in_array($confidence, self::ALL, true);
	}
}
