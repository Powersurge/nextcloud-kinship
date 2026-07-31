import axios from '@nextcloud/axios'
import { apiUrl } from './client'

export async function fetchFamilies(): Promise<Record<string, unknown>[]> {
	const { data } = await axios.get(apiUrl('/families'))
	return data.families ?? []
}

export async function createFamily(payload: unknown): Promise<Record<string, unknown>> {
	const { data } = await axios.post(apiUrl('/families'), { data: payload })
	return data
}

export async function updateFamily(id: number, payload: unknown): Promise<Record<string, unknown>> {
	const { data } = await axios.put(apiUrl(`/families/${id}`), { data: payload })
	return data
}

export async function deleteFamily(id: number): Promise<void> {
	await axios.delete(apiUrl(`/families/${id}`))
}
