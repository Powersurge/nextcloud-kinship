import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export const usePeopleStore = defineStore('peopleStore', () => {
	const selectedPerson = ref<Record<string, any> | null>(null)

	const setSelectedPerson = (person: Record<string, any> | null) => {
		selectedPerson.value = person
	}

	const showSidebar = ref(true)

	async function refreshSelectedPerson() {
		const id = selectedPerson.value?.id
		if (!id) {
			return
		}

		try {
			const response = await axios.get(
				generateUrl(`/apps/kinship/api/v1/people/${id}`),
			)
			selectedPerson.value = response.data
		} catch (e) {
			console.error('Failed to refresh selected person', e)
		}
	}

	return { selectedPerson, setSelectedPerson, showSidebar, refreshSelectedPerson }
})
