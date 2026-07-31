import axios from '@nextcloud/axios'
import { apiUrl } from './client'

export async function fetchRelationships(): Promise<Record<string, unknown>[]> {
	const { data } = await axios.get(apiUrl('/relationships'))
	return data.relationships ?? []
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
