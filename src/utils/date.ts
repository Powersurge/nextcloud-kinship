/**
 * Helpers for the genealogy date value object used across the API.
 *
 * The backend stores dates as `{ kind, start, end, display }` where `start`/
 * `end` are partial ISO strings ('YYYY', 'YYYY-MM', 'YYYY-MM-DD') and `display`
 * is the original human text. See lib/Db/DateValue.php.
 */
import type { GenealogyDateValue, GenealogyDateType } from '../types'

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

export function toGenealogyDate(value?: GenealogyDateValue | null): GenealogyDateType {
	const empty = createEmptyGenealogyDate()
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

export function createEmptyGenealogyDate(): GenealogyDateType {
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
