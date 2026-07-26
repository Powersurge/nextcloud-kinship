import type { DateKind } from '../constants.ts'

export interface Person {
	firstName: string;
	middleName: string;
	lastName: string;
	preferredName: string;
	birthDate: string;
}

export type GenologyDateType = {
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
