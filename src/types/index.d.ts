import type { DateKind } from '../constants.ts'

/**
 * A date as the API returns it (lib/Db/DateValue.php): numeric parts plus the
 * rendered `display` text. Forms edit the `GenealogyDateType` shape instead.
 */
export type GenealogyDateValue = {
	kind: DateKind | null,
	precision: string | null,
	start: {
		year: number | null,
		month: number | null,
		day: number | null,
	},
	end: {
		year: number | null,
		month: number | null,
		day: number | null,
	},
	display: string | null,
}

export interface Person {
	firstName: string;
	middleName: string;
	lastName: string;
	preferredName: string;
	/** Read from the person's birth event; null when they have none. */
	birthDate: GenealogyDateValue | null;
	/** Read from the person's death event; null when they have none. */
	deathDate: GenealogyDateValue | null;
}

export type GenealogyDateType = {
	start: {
		year: string | number,
		month: string | number,
		day: string | number,
	},
	end: {
		year: string | number,
		month: string | number,
		day: string | number,
	},
	kind: DateKind,
	precision: string,
}
