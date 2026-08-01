<?php

declare(strict_types=1);

namespace OCA\Kinship\Constants;

final class PlaceLookupProvider {
	public const NONE = 'none';
	public const NOMINATIM = 'nominatim';
	public const GOOGLE = 'google';

	public const ALL = [
		self::NONE,
		self::NOMINATIM,
		self::GOOGLE,
	];

	private function __construct() {
	}
}
