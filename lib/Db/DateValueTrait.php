<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

/**
 * Adds the shared `date_*` value-object columns to an Entity.
 *
 * Used by entities that carry a single genealogy date (Event, Relationship,
 * Fact). The four columns map to date_kind / date_start / date_end /
 * date_display. Setters go through the Entity magic setters so dirty-field
 * tracking still works.
 */
trait DateValueTrait {
	protected ?string $dateKind = null;
	protected ?string $datePrecision = null;
	protected ?int $dateStartYear = null;
	protected ?int $dateStartMonth = null;
	protected ?int $dateStartDay = null;
	protected ?int $dateEndYear = null;
	protected ?int $dateEndMonth = null;
	protected ?int $dateEndDay = null;

	public function getDate(): DateValue {
		return new DateValue(
			$this->dateKind,
			$this->datePrecision,
			$this->dateStartYear,
			$this->dateStartMonth,
			$this->dateStartDay,
			$this->dateEndYear,
			$this->dateEndMonth,
			$this->dateEndDay,
		);
	}

	public function setDate(DateValue|array $date): void {
		if ($date instanceof DateValue === false) {
			$date ??= new DateValue();
		}

		// Route through the Entity magic setters for dirty tracking.
		$this->setDateKind($date->kind);
		$this->setDatePrecision($date->precision);
		$this->setDateStartYear($date->start_year);
		$this->setDateStartMonth($date->start_month);
		$this->setDateStartDay($date->start_day);
		$this->setDateEndYear($date->end_year);
		$this->setDateEndMonth($date->end_month);
		$this->setDateEndDay($date->end_day);
	}
}
