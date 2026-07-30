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

export async function fetchPeopleOptions(
	queryParams: { searchQuery?: string, selected?: number[] } = {},
): Promise<SelectOption[]> {
	const response = await axios.get(generateUrl('/apps/kinship/api/v1/people'), {
		params: queryParams,
	})
	return (response.data.people ?? []).map(toPersonOption)
}

export async function fetchPlacesOptions(
	queryParams: { searchQuery?: string, selected?: number[] } = {},
): Promise<SelectOption[]> {
	const response = await axios.get(generateUrl('/apps/kinship/api/v1/places'), {
		params: queryParams,
	})
	return (response.data.places ?? []).map(toPlaceOption)
}
