/**
 * Helpers for the genealogy date value object used across the API.
 *
 * The backend stores dates as `{ kind, start, end, display }` where `start`/
 * `end` are partial ISO strings ('YYYY', 'YYYY-MM', 'YYYY-MM-DD') and `display`
 * is the original human text. See lib/Db/DateValue.php.
 */
import type { GenealogyDateValue, GenologyDateType } from '../types'

export type DateKind = 'exact' | 'approx' | 'range' | 'unknown'

export interface DateValue {
	kind: DateKind | null
	start: string | null
	end: string | null
	display: string | null
}

export function dateDisplay(value?: { display?: string | null } | null): string {
	return value?.display || ''
}

export function toGenologyDate(value?: GenealogyDateValue | null): GenologyDateType {
	const empty = createEmptyGenologyDate()
	if (!value) {
		return empty
	}

	return {
		kind: value.kind ?? empty.kind,
		precision: value.precision ?? empty.precision,
		start: {
			year: value.start?.year ?? '',
			month: value.start?.month ?? '',
			day: value.start?.day ?? '',
		},
		end: {
			year: value.end?.year ?? '',
			month: value.end?.month ?? '',
			day: value.end?.day ?? '',
		},
	}
}

export function toDateValue(iso: string | null): DateValue {
	if (!iso) {
		return { kind: 'unknown', start: null, end: null, display: null }
	}
	return { kind: 'exact', start: iso, end: null, display: iso }
}

export function startToDate(value?: DateValue | { start?: string | null } | null): Date | null {
	const start = value?.start
	return start ? new Date(start) : null
}

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
		kind: 'unknown',
		precision: 'year',
	}
}
