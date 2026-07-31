import axios from '@nextcloud/axios'
import type { Place, SelectOption } from '@/types'
import { apiUrl } from './client'

export type PlacesQueryParams = {
	searchQuery?: string
	selected?: number[]
	[key: string]: unknown
}

function toPlaceOption(place: Place): SelectOption {
	return {
		value: place.id,
		label: place.fullName || place.name || `#${place.id}`,
	}
}

export async function fetchPlaces(params?: PlacesQueryParams): Promise<Place[]> {
	const { data } = await axios.get(apiUrl('/places'), { params })
	return data.places ?? []
}

/** Places list mapped for NcSelect / option pickers. */
export async function fetchPlacesOptions(
	queryParams: PlacesQueryParams = {},
): Promise<SelectOption[]> {
	const places = await fetchPlaces(queryParams)
	return places.map(toPlaceOption)
}
