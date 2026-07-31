import axios from '@nextcloud/axios'
import type { Event, PaginatedResult } from '@/types'
import { apiUrl } from './client'

export type PersonEventsParams = {
	type?: string
	limit?: number
	offset?: number
	[key: string]: unknown
}

export async function fetchEvents(): Promise<Event[]> {
	const { data } = await axios.get(apiUrl('/events'))
	return data.events ?? []
}

export async function createEvent(payload: unknown): Promise<Event> {
	const { data } = await axios.post(apiUrl('/events'), { data: payload })
	return data
}

export async function updateEvent(id: number, payload: unknown): Promise<Event> {
	const { data } = await axios.put(apiUrl(`/events/${id}`), { data: payload })
	return data
}

export async function deleteEvent(id: number): Promise<void> {
	await axios.delete(apiUrl(`/events/${id}`))
}

export async function fetchPersonEvents(
	personId: number,
	params?: PersonEventsParams,
): Promise<PaginatedResult<Event>> {
	const { data } = await axios.get<PaginatedResult<Event>>(
		apiUrl(`/people/${personId}/events`),
		{ params },
	)
	return {
		events: data.events ?? [],
		total: data.total ?? data.events?.length ?? 0,
		limit: data.limit ?? params?.limit ?? 0,
		offset: data.offset ?? params?.offset ?? 0,
	}
}
