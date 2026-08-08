<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;

/**
 * Genealogy date value object.
 *
 * Supports the four v0.1 date kinds: exact, about(approximate), range, unknown.
 * `start` / `end` hold partial ISO 8601 strings ('YYYY', 'YYYY-MM', 'YYYY-MM-DD').
 * `end` is only meaningful for the `range` kind. `display` keeps the original
 * human-entered text (e.g. "abt. 1901", "between 1900 and 1905").
 */
class DateValue implements JsonSerializable {

	public const KIND_BEFORE = 'before';
	public const KIND_AFTER = 'after';
	public const KIND_EXACT = 'exact';
	public const KIND_ABOUT = 'about';
	public const KIND_BETWEEN = 'between';
	public const KIND_UNKNOWN = 'unknown';
	public const PRECISION_YEAR = 'year';

	public const PRECISION_YEAR_MONTH = 'yearMonth';
	public const PRECISION_YEAR_MONTH_DAY = 'yearMonthDay';

	public const KINDS = [
		self::KIND_UNKNOWN,
		self::KIND_BEFORE,
		self::KIND_AFTER,
		self::KIND_EXACT,
		self::KIND_BETWEEN,
		self::KIND_ABOUT,
	];

	public const PRECISIONS = [
		self::PRECISION_YEAR,
		self::PRECISION_YEAR_MONTH,
		self::PRECISION_YEAR_MONTH_DAY,
	];

	public function __construct(
		public ?string $kind = null,
		public ?string $precision = null,
		public ?int $start_year = null,
		public ?int $start_month = null,
		public ?int $start_day = null,
		public ?int $end_year = null,
		public ?int $end_month = null,
		public ?int $end_day = null,
	) {
	}

	/**
	 * Build a DateValue from a decoded JSON/array payload.
	 *
	 * @param array<string, mixed>|null $data
	 */
	public static function fromArray(?array $data): self {
		if ($data === null) {
			return new self();
		}

		$kind = isset($data['kind']) && in_array($data['kind'], self::KINDS, true)
			? $data['kind']
			: null;

		$precision = isset($data['precision']) && in_array($data['precision'], self::PRECISIONS, true)
			? $data['precision']
			: null;

		return new self(
			$kind,
			$precision,
			!empty($data['start']['year']) ? (int)$data['start']['year'] : null,
			!empty($data['start']['month']) ? (int)$data['start']['month'] : null,
			!empty($data['start']['day']) ? (int)$data['start']['day'] : null,
			!empty($data['end']['year']) ? (int)$data['end']['year'] : null,
			!empty($data['end']['month']) ? (int)$data['end']['month'] : null,
			!empty($data['end']['day']) ? (int)$data['end']['day'] : null,
		);
	}

	public function isEmpty(): bool {
		if (empty($this->kind) || empty($this->precision)) {
			return true;
		}

		if ($this->kind === self::KIND_UNKNOWN) {
			return true;
		}

		if (empty($this->start_year)) {
			return true;
		}

		return false;
	}

	/**
	 * Partial ISO 8601 string for the start date ('YYYY', 'YYYY-MM' or
	 * 'YYYY-MM-DD'), for cheap storage/sorting outside the full value object.
	 */
	public function getStartIso(): ?string {
		return self::toIso($this->start_year, $this->start_month, $this->start_day);
	}

	public function getEndIso(): ?string {
		return self::toIso($this->end_year, $this->end_month, $this->end_day);
	}

	private static function toIso(?int $year, ?int $month, ?int $day): ?string {
		if ($year === null) {
			return null;
		}

		$iso = sprintf('%04d', $year);
		if ($month !== null) {
			$iso .= sprintf('-%02d', $month);
			if ($day !== null) {
				$iso .= sprintf('-%02d', $day);
			}
		}

		return $iso;
	}

	/**
	 * Human-readable rendering of the date, honoring `kind` (exact/about/
	 * between/before/after/unknown).
	 */
	public function getDisplayText(): ?string {
		if ($this->isEmpty()) {
			return null;
		}

		$start = $this->getStartIso();
		$end = $this->getEndIso();

		return match ($this->kind) {
			self::KIND_ABOUT => $start !== null ? 'abt. ' . $start : null,
			self::KIND_BEFORE => $start !== null ? 'bef. ' . $start : null,
			self::KIND_AFTER => $start !== null ? 'aft. ' . $start : null,
			self::KIND_BETWEEN => $start !== null && $end !== null
				? 'Between ' . $start . ' and ' . $end
				: $start,
			default => $start,
		};
	}

	/**
	 * @return array<string, mixed>
	 */
	public function jsonSerialize(): array {
		return [
			'kind' => $this->kind,
			'precision' => $this->precision,
			'start' => [
				'year' => $this->start_year,
				'month' => $this->start_month,
				'day' => $this->start_day,
			],
			'end' => [
				'year' => $this->end_year,
				'month' => $this->end_month,
				'day' => $this->end_day,
			],
			'display' => $this->getDisplayText(),
		];
	}
}
