import axios from '@nextcloud/axios'
import type { Fact } from '@/types'
import { apiUrl } from './client'

export type FactsQueryParams = {
	subjectType: string
	subjectId: number
}

export async function fetchFacts(params: FactsQueryParams): Promise<Fact[]> {
	const { data } = await axios.get(apiUrl('/facts'), { params })
	return data.facts ?? []
}

export async function createFact(payload: unknown): Promise<Fact> {
	const { data } = await axios.post(apiUrl('/facts'), { data: payload })
	return data
}

export async function updateFact(id: number, payload: unknown): Promise<Fact> {
	const { data } = await axios.put(apiUrl(`/facts/${id}`), { data: payload })
	return data
}

export async function deleteFact(id: number): Promise<void> {
	await axios.delete(apiUrl(`/facts/${id}`))
}
