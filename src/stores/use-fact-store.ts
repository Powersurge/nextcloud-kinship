import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Fact, GenealogyDateType } from '@/types'
import { createEmptyGenealogyDate, toGenealogyDate } from '@/utils/date.ts'
import { fetchFacts } from '@/api/facts'

export interface FactFormData {
	id?: number
	subjectType: string
	subjectId: number
	type: string
	value: string | null
	placeId: number | null
	date: GenealogyDateType
	note: string | null
}

export const useFactStore = defineStore('factStore', () => {
	const facts = ref<Fact[]>([])
	const loading = ref(false)
	const subjectId = ref<number | null>(null)
	const showFormModal = ref(false)
	const factForm = ref<FactFormData | Record<string, unknown>>({})

	async function loadFacts(subjectType: string, id: number) {
		subjectId.value = id
		loading.value = true
		try {
			facts.value = await fetchFacts({ subjectType, subjectId: id })
		} catch (e) {
			console.error('Failed to load facts', e)
			facts.value = []
		} finally {
			loading.value = false
		}
	}

	function reset() {
		facts.value = []
		subjectId.value = null
		loading.value = false
	}

	function openFactForm(formData: FactFormData | Record<string, unknown>) {
		factForm.value = formData
		showFormModal.value = true
	}

	function openCreateFactForm(personId: number) {
		openFactForm({
			subjectType: 'person',
			subjectId: personId,
			type: 'occupation',
			value: null,
			placeId: null,
			date: createEmptyGenealogyDate(),
			note: null,
		})
	}

	function openEditFactForm(fact: Fact) {
		openFactForm({
			...fact,
			date: toGenealogyDate(fact.date),
		})
	}

	return {
		facts,
		loading,
		subjectId,
		showFormModal,
		factForm,
		loadFacts,
		reset,
		openFactForm,
		openCreateFactForm,
		openEditFactForm,
	}
})
