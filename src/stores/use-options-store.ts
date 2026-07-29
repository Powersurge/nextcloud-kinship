import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Person, Place, SelectOption } from '@/types'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

function toPersonOption(person: Person): SelectOption {
	return {
		value: person.id,
		label: person.fullName
			|| `${person.firstName ?? ''} ${person.lastName ?? ''}`.trim()
			|| `#${person.id}`,
	}
}

function toPlaceOption(place: Place): SelectOption {
	return {
		value: place.id,
		label: place.fullName || place.name || `#${place.id}`,
	}
}

export const useOptionsStore = defineStore('optionsStore', () => {
	const people = ref<SelectOption[]>([])
	const places = ref<SelectOption[]>([])

	const loadPeople = async (searchQuery?: string) => {
		const response = await axios.get(generateUrl('/apps/kinship/api/v1/people'), {
			params: {
				searchQuery,
			},
		})
		people.value = (response.data.people ?? []).map(toPersonOption)
	}

	const loadPlaces = async (searchQuery?: string) => {
		const response = await axios.get(generateUrl('/apps/kinship/api/v1/places'), {
			params: {
				searchQuery,
			},
		})
		places.value = (response.data.places ?? []).map(toPlaceOption)
	}

	return {
		people,
		loadPeople,
		places,
		loadPlaces,
	}
})
