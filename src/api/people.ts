import axios from '@nextcloud/axios'
import type { Person, PersonFamily, SelectOption } from '@/types'
import { apiUrl } from './client'

export type PeopleQueryParams = {
	searchQuery?: string
	selected?: number[]
	[key: string]: unknown
}

function toPersonOption(person: Person): SelectOption {
	return {
		value: person.id,
		label: person.fullName
			|| `${person.firstName ?? ''} ${person.lastName ?? ''}`.trim()
			|| `#${person.id}`,
	}
}

export async function fetchPeople(params?: PeopleQueryParams): Promise<Person[]> {
	const { data } = await axios.get(apiUrl('/people'), { params })
	return data.people ?? []
}

export async function fetchPerson(id: number): Promise<Person> {
	const { data } = await axios.get(apiUrl(`/people/${id}`))
	return data
}

export async function createPerson(payload: unknown): Promise<Person> {
	const { data } = await axios.post(apiUrl('/people'), { data: payload })
	return data
}

export async function updatePerson(id: number, payload: unknown): Promise<Person> {
	const { data } = await axios.put(apiUrl(`/people/${id}`), { data: payload })
	return data
}

export async function deletePerson(id: number): Promise<void> {
	await axios.delete(apiUrl(`/people/${id}`))
}

export async function fetchPersonFamily(id: number): Promise<PersonFamily> {
	const { data } = await axios.get(apiUrl(`/people/${id}/family`))
	return {
		personId: data.personId ?? id,
		activePartnerships: data.activePartnerships ?? [],
		endedPartnerships: data.endedPartnerships ?? [],
		children: data.children ?? [],
		parents: data.parents ?? [],
	}
}

/** People list mapped for NcSelect / option pickers. */
export async function fetchPeopleOptions(
	queryParams: PeopleQueryParams = {},
): Promise<SelectOption[]> {
	const people = await fetchPeople(queryParams)
	return people.map(toPersonOption)
}
