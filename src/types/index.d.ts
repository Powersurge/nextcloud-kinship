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
	value: number | string,
	label: string,
}

/** Compact place nested on people, events, and facts. */
export type PlaceSummary = {
	id: number
	name: string
	fullName: string | null
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
	birthPlace?: PlaceSummary | null;
	deathPlace?: PlaceSummary | null;
}

export type FamilyRelationship = Record<string, any> & {
	id: number
	relatedPersonId: number
	relatedPersonName: string
}

export type PersonFamily = {
	personId: number | null
	activePartnerships: FamilyRelationship[]
	endedPartnerships: FamilyRelationship[]
	children: FamilyRelationship[]
	parents: FamilyRelationship[]
}

export interface Event {
	id?: number | null;
	type: string;
	title: string | null;
	description: string | null;
	date: GenealogyDateValue | null;
	placeId: number | null;
	place?: PlaceSummary | null;
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
	latitude?: string | null;
	longitude?: string | null;
	provider?: string | null;
	providerPlaceId?: string | null;
}

export type PlaceLookupResult = {
	id: number | null
	name: string
	fullName: string | null
	latitude: string | null
	longitude: string | null
	provider: string | null
	providerPlaceId: string | null
	source: 'local' | 'external' | 'manual'
}

export interface Fact {
	id: number;
	subjectType: string;
	subjectId: number;
	type: string;
	value: string | null;
	placeId: number | null;
	place?: PlaceSummary | null;
	date: GenealogyDateValue | null;
	note: string | null;
}

export interface Source {
	id: number;
	title: string;
	author: string | null;
	publication: string | null;
	repository: string | null;
	sourceType: string | null;
	url: string | null;
	notes: string | null;
	ownerUid?: string | null;
	createdBy?: string | null;
	createdAt?: number | null;
	updatedAt?: number | null;
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
