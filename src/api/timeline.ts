import axios from '@nextcloud/axios'
import type { Event } from '@/types'
import { apiUrl } from './client'

/** Legacy timeline endpoint; prefer fetchPersonEvents from api/events. */
export async function fetchPersonTimeline(personId: number): Promise<Event[]> {
	const { data } = await axios.get(apiUrl(`/timeline/person/${personId}`))
	return data
}
