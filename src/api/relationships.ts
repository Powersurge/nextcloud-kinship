import axios from '@nextcloud/axios'
import type { Person } from '@/types'
import { apiUrl } from './client'

export type DerivedRelationships = {
	parents: Person[]
	children: Person[]
	spouses: Person[]
	formerSpouses: Person[]
	siblings: Person[]
	grandparents: Person[]
	grandchildren: Person[]
	[key: string]: Person[]
}

export async function fetchRelationships(): Promise<Record<string, unknown>[]> {
	const { data } = await axios.get(apiUrl('/relationships'))
	return data.relationships ?? []
}

export async function fetchPersonDerived(personId: number): Promise<DerivedRelationships> {
	const { data } = await axios.get(apiUrl(`/people/${personId}/relationships/derived`))
	const derived = data.derived ?? {}
	return {
		parents: derived.parents ?? [],
		children: derived.children ?? [],
		spouses: derived.spouses ?? [],
		formerSpouses: derived.formerSpouses ?? [],
		siblings: derived.siblings ?? [],
		grandparents: derived.grandparents ?? [],
		grandchildren: derived.grandchildren ?? [],
		...derived,
	}
}

export async function createRelationship(payload: unknown): Promise<Record<string, unknown>> {
	const { data } = await axios.post(apiUrl('/relationships'), { data: payload })
	return data
}

export async function updateRelationship(
	id: number,
	payload: unknown,
): Promise<Record<string, unknown>> {
	const { data } = await axios.put(apiUrl(`/relationships/${id}`), { data: payload })
	return data
}

export async function deleteRelationship(id: number): Promise<void> {
	await axios.delete(apiUrl(`/relationships/${id}`))
}
