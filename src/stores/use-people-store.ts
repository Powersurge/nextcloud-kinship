import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export const usePeopleStore = defineStore('peopleStore', () => {
	const selectedPerson = ref<Record<string, any> | null>(null)
	const loading = ref(false)

	async function loadPersonSummary(id: number) {
		loading.value = true
		try {
			const response = await axios.get(
				generateUrl(`/apps/kinship/api/v1/people/${id}`),
			)
			selectedPerson.value = response.data
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

		await axios.put(
			generateUrl(`/apps/kinship/api/v1/people/${id}`),
			{ data: { biography } },
		)
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
