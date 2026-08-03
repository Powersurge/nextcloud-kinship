import axios from '@nextcloud/axios'
import type { Source } from '@/types'
import { apiUrl } from './client'

export async function fetchSources(): Promise<Source[]> {
	const { data } = await axios.get(apiUrl('/sources'))
	return data.sources ?? []
}

export async function fetchSource(id: number): Promise<Source> {
	const { data } = await axios.get(apiUrl(`/sources/${id}`))
	return data
}

export async function createSource(payload: unknown): Promise<Source> {
	const { data } = await axios.post(apiUrl('/sources'), { data: payload })
	return data
}

export async function updateSource(id: number, payload: unknown): Promise<Source> {
	const { data } = await axios.put(apiUrl(`/sources/${id}`), { data: payload })
	return data
}

export async function deleteSource(id: number): Promise<void> {
	await axios.delete(apiUrl(`/sources/${id}`))
}
