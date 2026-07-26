export const DATETIME_FORMAT = 'MMMM dd, yyyy'

export const DATE_KINDS = {
	Unknown: 'unknown',
	Before: 'before',
	After: 'after',
	Exact: 'exact',
	About: 'about',
	Between: 'between',
} as const

export const DATE_KIND_OPTIONS = Object.entries({ ...DATE_KINDS })
	.map(([k, v]) => ({
		label: k,
		value: v,
	}))

export type DateKind = typeof DATE_KINDS[keyof typeof DATE_KINDS];
