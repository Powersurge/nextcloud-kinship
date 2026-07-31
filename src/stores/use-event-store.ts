import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Event, GenealogyDateType } from '@/types'
import { createEmptyGenealogyDate, toGenealogyDate } from '@/utils/date.ts'
import { fetchPersonEvents } from '@/api/events'

export interface EventFormData {
	id?: number
	type: string
	title: string | null
	description: string | null
	date: GenealogyDateType
	placeId: number | null
	subjectId?: number | null
}

export const useEventStore = defineStore('eventStore', () => {
	const showFormModal = ref(false)
	const eventForm = ref<EventFormData | Record<string, unknown>>({})

	const openEventForm = (formData: EventFormData | Record<string, unknown>) => {
		eventForm.value = formData
		showFormModal.value = true
	}

	const openLifeDateForm = async (
		type: 'birth' | 'death',
		person: { id: number },
	) => {
		let existing: Event | null = null

		try {
			const result = await fetchPersonEvents(person.id, {
				type,
				limit: 1,
				offset: 0,
			})
			existing = result.events?.[0] ?? null
		} catch (e) {
			console.error('Failed to load life event', e)
		}

		if (existing) {
			openEventForm({
				id: existing.id ?? undefined,
				type: existing.type,
				title: existing.title ?? null,
				description: existing.description ?? null,
				date: toGenealogyDate(existing.date),
				placeId: existing.placeId ?? null,
			})
			return
		}

		openEventForm({
			type,
			title: null,
			description: null,
			date: createEmptyGenealogyDate(),
			placeId: null,
			subjectId: person.id,
		})
	}

	return { showFormModal, openEventForm, openLifeDateForm, eventForm }
})
