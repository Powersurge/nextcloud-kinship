import { defineStore } from 'pinia'
import { ref } from 'vue'
import { fetchPerson, updatePerson } from '@/api/people'

export const usePeopleStore = defineStore('peopleStore', () => {
	const selectedPerson = ref<Record<string, any> | null>(null)
	const loading = ref(false)

	async function loadPersonSummary(id: number) {
		loading.value = true
		try {
			selectedPerson.value = await fetchPerson(id)
		} catch (e) {
			console.error('Failed to load person summary', e)
			selectedPerson.value = null
		} finally {
			loading.value = false
		}
	}

	async function refreshSelectedPerson() {
		const id = selectedPerson.value?.id
		if (!id) {
			return
		}

		await loadPersonSummary(id)
	}

	async function updateBiography(biography: string | null) {
		const id = selectedPerson.value?.id
		if (!id) {
			return
		}

		await updatePerson(id, { biography })
		await refreshSelectedPerson()
	}

	function clearSelectedPerson() {
		selectedPerson.value = null
	}

	return {
		selectedPerson,
		loading,
		loadPersonSummary,
		refreshSelectedPerson,
		updateBiography,
		clearSelectedPerson,
	}
})
