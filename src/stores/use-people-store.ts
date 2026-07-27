import { defineStore } from 'pinia'
import { ref } from 'vue'

export const usePeopleStore = defineStore('peopleStore', () => {
	const selectedPerson = ref(null)

	const setSelectedPerson = (person: any) => {
		selectedPerson.value = person
	}

	const showSidebar = ref(true)

	return { selectedPerson, setSelectedPerson, showSidebar }
})
