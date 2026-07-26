/**
 * Helpers for the genealogy date value object used across the API.
 *
 * The backend stores dates as `{ kind, start, end, display }` where `start`/
 * `end` are partial ISO strings ('YYYY', 'YYYY-MM', 'YYYY-MM-DD') and `display`
 * is the original human text. See lib/Db/DateValue.php.
 */
import type { GenologyDateType } from '../types'

export type DateKind = 'exact' | 'approx' | 'range' | 'unknown'

export interface DateValue {
	kind: DateKind | null
	start: string | null
	end: string | null
	display: string | null
}

/**
 * Human-readable label for a date value (empty string when unknown).
 * @param value
 */
export function dateDisplay(value?: DateValue | null): string {
	if (!value) {
		return ''
	}
	return value.display || value.start || ''
}

/**
 * Just the sortable start of a cached person birth/death date.
 * @param value
 */
export function cachedDisplay(value?: { start?: string | null, display?: string | null } | null): string {
	if (!value) {
		return ''
	}
	return value.display || value.start || ''
}

/**
 * Build a DateValue payload from a single ISO date string (create/edit forms
 * currently capture an exact day). Returns an unknown-kind value when empty.
 * @param iso
 */
export function toDateValue(iso: string | null): DateValue {
	if (!iso) {
		return { kind: 'unknown', start: null, end: null, display: null }
	}
	return { kind: 'exact', start: iso, end: null, display: iso }
}

/**
 * Parse the `start` of a date value into a JS Date for NcDateTimePicker.
 * @param value
 */
export function startToDate(value?: DateValue | { start?: string | null } | null): Date | null {
	const start = value?.start
	return start ? new Date(start) : null
}

/**
 * Format a JS Date as an ISO day string (YYYY-MM-DD).
 * @param value
 */
export function fromDate(value: Date | null): string | null {
	if (!value) {
		return null
	}
	const year = value.getFullYear()
	const month = String(value.getMonth() + 1).padStart(2, '0')
	const day = String(value.getDate()).padStart(2, '0')
	return `${year}-${month}-${day}`
}

export function createEmptyGenologyDate(): GenologyDateType {
	return {
		start: {
			year: '',
			month: '',
			day: '',
		},
		end: {
			year: '',
			month: '',
			day: '',
		},
		kind: 'between',
		precision: 'year',
	}
}
