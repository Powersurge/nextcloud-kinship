import axios from '@nextcloud/axios'
import type { Citation } from '@/types'
import { apiUrl } from './client'

export type CitationsQueryParams = {
	subjectType: string
	subjectId: number
}

export async function fetchCitations(params: CitationsQueryParams): Promise<Citation[]> {
	const { data } = await axios.get(apiUrl('/citations'), { params })
	return data.citations ?? []
}

export async function createCitation(payload: unknown): Promise<Citation> {
	const { data } = await axios.post(apiUrl('/citations'), { data: payload })
	return data
}

export async function updateCitation(id: number, payload: unknown): Promise<Citation> {
	const { data } = await axios.put(apiUrl(`/citations/${id}`), { data: payload })
	return data
}

export async function deleteCitation(id: number): Promise<void> {
	await axios.delete(apiUrl(`/citations/${id}`))
}
