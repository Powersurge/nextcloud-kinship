import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { GenealogyDateType } from '@/types'
import { createEmptyGenealogyDate, toGenealogyDate } from '@/utils/date.ts'

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

	const openLifeDateForm = (
		type: 'birth' | 'death',
		person: Record<string, any>,
	) => {
		const existing = (person.events ?? []).find(
			(event: Record<string, any>) => event.type === type,
		)

		if (existing) {
			openEventForm({
				id: existing.id,
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
