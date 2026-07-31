export const DATETIME_FORMAT = 'MMMM dd, yyyy'

export const LIVING_STATUSES = {
	living: 'Living',
	deceased: 'Deceased',
	unknown: 'Unknown',
}
export type LivingStatus = keyof typeof LIVING_STATUSES;

export const DATE_KINDS = {
	Unknown: 'unknown',
	Exact: 'exact',
	Before: 'before',
	After: 'after',
	About: 'about',
	Between: 'between',
} as const

export const DATE_KIND_OPTIONS = Object.entries({ ...DATE_KINDS })
	.map(([k, v]) => ({
		label: k,
		value: v,
	}))

export type DateKind = typeof DATE_KINDS[keyof typeof DATE_KINDS];

export const DATE_PRECISION_OPTIONS = [
	{ value: 'year', label: 'Year' },
	{ value: 'yearMonth', label: 'Year, Month' },
	{ value: 'yearMonthDay', label: 'Year, Month, Day' },
]

export const FACT_TYPE_OPTIONS = [
	{ value: 'occupation', label: 'Occupation' },
	{ value: 'religion', label: 'Religion' },
	{ value: 'nationality', label: 'Nationality' },
	{ value: 'residence', label: 'Residence' },
	{ value: 'physical_description', label: 'Physical description' },
	{ value: 'name_variant', label: 'Name variant' },
]
