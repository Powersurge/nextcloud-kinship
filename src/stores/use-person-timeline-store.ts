import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import type { Event } from '@/types'
import { fetchPersonEvents } from '@/api/events'

const PAGE_SIZE = 10

export const usePersonTimelineStore = defineStore('personTimelineStore', () => {
	const personId = ref<number | null>(null)
	const events = ref<Event[]>([])
	const total = ref(0)
	const loading = ref(false)
	const pageSize = ref(PAGE_SIZE)

	const hasMore = computed(() => events.value.length < total.value)

	function reset() {
		personId.value = null
		events.value = []
		total.value = 0
		loading.value = false
	}

	async function fetchPage(id: number, offset: number, append: boolean) {
		loading.value = true
		try {
			const result = await fetchPersonEvents(id, {
				limit: pageSize.value,
				offset,
			})

			const pageEvents = result.events ?? []
			total.value = result.total ?? pageEvents.length

			if (append) {
				events.value = [...events.value, ...pageEvents]
			} else {
				events.value = pageEvents
			}
		} catch (e) {
			console.error('Failed to load person timeline', e)
			if (!append) {
				events.value = []
				total.value = 0
			}
		} finally {
			loading.value = false
		}
	}

	async function loadInitial(id: number) {
		personId.value = id
		events.value = []
		total.value = 0
		await fetchPage(id, 0, false)
	}

	async function loadNextPage() {
		const id = personId.value
		if (!id || loading.value || !hasMore.value) {
			return
		}

		await fetchPage(id, events.value.length, true)
	}

	async function reloadForPerson(id: number) {
		if (personId.value === id) {
			await loadInitial(id)
		}
	}

	function removeEvent(id: number) {
		const before = events.value.length
		events.value = events.value.filter((event) => event.id !== id)
		if (events.value.length < before) {
			total.value = Math.max(0, total.value - 1)
		}
	}

	return {
		personId,
		events,
		total,
		loading,
		pageSize,
		hasMore,
		reset,
		loadInitial,
		loadNextPage,
		reloadForPerson,
		removeEvent,
	}
})
