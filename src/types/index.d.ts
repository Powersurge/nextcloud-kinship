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

export type SelectOption = {
	value: number,
	label: string,
}

export interface Person {
	id: number;
	fullName?: string;
	firstName: string;
	middleName: string;
	lastName: string;
	preferredName: string;
	gender?: string | null;
	livingStatus?: string;
	biography?: string | null;
	avatar?: string | null;
	birthDate: GenealogyDateValue | null;
	deathDate: GenealogyDateValue | null;
}

export interface Event {
	id?: number | null;
	type: string;
	title: string | null;
	description: string | null;
	date: GenealogyDateValue | null;
	placeId: number | null;
	customEventId?: number | null;
	familyId?: number | null;
}

export interface PaginatedResult<T> {
	events: T[];
	total: number;
	limit: number;
	offset: number;
}

export interface Place {
	id: number;
	name: string;
	fullName: string | null;
}

export interface Fact {
	id: number;
	subjectType: string;
	subjectId: number;
	type: string;
	value: string | null;
	placeId: number | null;
	date: GenealogyDateValue | null;
	note: string | null;
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
