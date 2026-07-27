<?php

declare(strict_types=1);

namespace OCA\Kinship\Import;

use Gedcom\Record\Birt;
use Gedcom\Record\Deat;
use Gedcom\Record\Indi\Even as IndiEven;
use OCA\Kinship\Db\DateValue;

/**
 * Converts GEDCOM date strings and birth/death records into Kinship DateValue
 * payloads.
 */
final class GedcomDateParser {
	/**
	 * @return array<string, mixed>|null
	 */
	public static function fromIndiEvent(?IndiEven $event): ?array {
		if ($event === null) {
			return null;
		}

		$dateString = $event->getDate();
		if (!is_string($dateString) || trim($dateString) === '') {
			return null;
		}

		return self::fromDateString($dateString);
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public static function fromBirthOrDeath(Birt|Deat|null $record): ?array {
		if ($record === null) {
			return null;
		}

		$dateString = $record->getDate();
		if (!is_string($dateString) || trim($dateString) === '') {
			return null;
		}

		return self::fromDateString($dateString, $record);
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public static function fromDateString(
		?string $dateString,
		Birt|Deat|null $record = null,
	): ?array {
		if ($dateString === null || trim($dateString) === '') {
			return null;
		}

		$isoPayload = self::fromIsoDateString(trim($dateString));
		if ($isoPayload !== null) {
			return $isoPayload;
		}

		$dateString = strtoupper(trim($dateString));
		$parts = preg_split('/\s+/', $dateString) ?: [];

		$kind = DateValue::KIND_EXACT;
		$startYear = null;
		$startMonth = null;
		$startDay = null;
		$endYear = null;
		$endMonth = null;
		$endDay = null;

		if (($parts[0] ?? '') === 'ABT' || ($parts[0] ?? '') === 'EST' || ($parts[0] ?? '') === 'CAL') {
			$kind = DateValue::KIND_ABOUT;
			array_shift($parts);
		} elseif (($parts[0] ?? '') === 'BEF') {
			$kind = DateValue::KIND_BEFORE;
			array_shift($parts);
		} elseif (($parts[0] ?? '') === 'AFT') {
			$kind = DateValue::KIND_AFTER;
			array_shift($parts);
		} elseif (($parts[0] ?? '') === 'BET') {
			$kind = DateValue::KIND_BETWEEN;
			array_shift($parts);
			$range = self::parseBetweenRange($parts);
			if ($range !== null) {
				return $range;
			}
		} elseif (($parts[0] ?? '') === 'FROM') {
			array_shift($parts);
			$range = self::parseFromToRange($parts);
			if ($range !== null) {
				return $range;
			}
		}

		if ($record instanceof Birt || $record instanceof Deat) {
			$year = $record->getYear();
			if ($year > 0) {
				$startYear = $year;
			}
			$month = $record->getMonth();
			if ($month !== null && $month !== '') {
				$startMonth = (int)$month;
			}
			$day = $record->getDay();
			if ($day !== null) {
				$startDay = $day;
			}
		} else {
			$parsed = self::parseDateParts($parts);
			$startYear = $parsed['year'];
			$startMonth = $parsed['month'];
			$startDay = $parsed['day'];
		}

		if ($startYear === null) {
			return null;
		}

		return self::toPayload(
			$kind,
			$startYear,
			$startMonth,
			$startDay,
			$endYear,
			$endMonth,
			$endDay,
		);
	}

	/**
	 * @param list<string> $parts
	 * @return array<string, mixed>|null
	 */
	private static function parseBetweenRange(array $parts): ?array {
		$andIndex = array_search('AND', $parts, true);
		if ($andIndex === false) {
			return null;
		}

		$start = self::parseDateParts(array_slice($parts, 0, $andIndex));
		$end = self::parseDateParts(array_slice($parts, $andIndex + 1));

		if ($start['year'] === null || $end['year'] === null) {
			return null;
		}

		return self::toPayload(
			DateValue::KIND_BETWEEN,
			$start['year'],
			$start['month'],
			$start['day'],
			$end['year'],
			$end['month'],
			$end['day'],
		);
	}

	/**
	 * @param list<string> $parts
	 * @return array<string, mixed>|null
	 */
	private static function parseFromToRange(array $parts): ?array {
		$toIndex = array_search('TO', $parts, true);
		if ($toIndex === false) {
			return null;
		}

		$start = self::parseDateParts(array_slice($parts, 0, $toIndex));
		$end = self::parseDateParts(array_slice($parts, $toIndex + 1));

		if ($start['year'] === null || $end['year'] === null) {
			return null;
		}

		return self::toPayload(
			DateValue::KIND_BETWEEN,
			$start['year'],
			$start['month'],
			$start['day'],
			$end['year'],
			$end['month'],
			$end['day'],
		);
	}

	/**
	 * @param list<string> $parts
	 * @return array{year: ?int, month: ?int, day: ?int}
	 */
	/**
	 * @return array<string, mixed>|null
	 */
	private static function fromIsoDateString(string $dateString): ?array {
		if (preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $dateString, $matches) !== 1) {
			return null;
		}

		$year = (int)$matches[1];
		$month = isset($matches[2]) && $matches[2] !== '' ? (int)$matches[2] : null;
		$day = isset($matches[3]) && $matches[3] !== '' ? (int)$matches[3] : null;

		return self::toPayload(
			DateValue::KIND_EXACT,
			$year,
			$month,
			$day,
			null,
			null,
			null,
		);
	}

	private static function parseDateParts(array $parts): array {
		$months = [
			'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MAY' => 5, 'JUN' => 6,
			'JUL' => 7, 'AUG' => 8, 'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12,
		];

		$year = null;
		$month = null;
		$day = null;

		foreach ($parts as $part) {
			if (isset($months[$part])) {
				$month = $months[$part];
				continue;
			}

			if (ctype_digit($part)) {
				$value = (int)$part;
				if ($value >= 1000) {
					$year = $value;
				} elseif ($value >= 1 && $value <= 31 && $day === null) {
					$day = $value;
				}
			}
		}

		return [
			'year' => $year,
			'month' => $month,
			'day' => $day,
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function toPayload(
		string $kind,
		int $startYear,
		?int $startMonth,
		?int $startDay,
		?int $endYear,
		?int $endMonth,
		?int $endDay,
	): array {
		$precision = DateValue::PRECISION_YEAR;
		if ($startDay !== null) {
			$precision = DateValue::PRECISION_YEAR_MONTH_DAY;
		} elseif ($startMonth !== null) {
			$precision = DateValue::PRECISION_YEAR_MONTH;
		}

		$payload = [
			'kind' => $kind,
			'precision' => $precision,
			'start' => [
				'year' => $startYear,
				'month' => $startMonth,
				'day' => $startDay,
			],
		];

		if ($endYear !== null) {
			$payload['end'] = [
				'year' => $endYear,
				'month' => $endMonth,
				'day' => $endDay,
			];
		}

		return $payload;
	}
}
