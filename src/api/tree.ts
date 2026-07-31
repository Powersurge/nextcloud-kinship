import axios from '@nextcloud/axios'
import { apiUrl } from './client'

export async function fetchTree(personId: number): Promise<Record<string, unknown>> {
	const { data } = await axios.get(apiUrl(`/tree/${personId}`))
	return data
}
