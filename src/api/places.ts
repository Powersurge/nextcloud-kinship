import axios from '@nextcloud/axios'
import type { Place, PlaceLookupResult, SelectOption } from '@/types'
import { apiUrl } from './client'

export type PlacesQueryParams = {
	searchQuery?: string
	selected?: number[]
	[key: string]: unknown
}

export type PlaceLookupOption = SelectOption & {
	id: number | null
	name: string
	fullName: string | null
	latitude: string | null
	longitude: string | null
	provider: string | null
	providerPlaceId: string | null
	source: 'local' | 'external' | 'manual'
}

function toPlaceOption(place: Place): SelectOption {
	return {
		value: place.id,
		label: place.fullName || place.name || `#${place.id}`,
	}
}

function toLookupOption(result: PlaceLookupResult): PlaceLookupOption {
	const value = result.id
		?? `ext:${result.provider ?? 'manual'}:${result.providerPlaceId ?? result.fullName ?? result.name}`

	return {
		value,
		label: result.fullName || result.name,
		id: result.id,
		name: result.name,
		fullName: result.fullName,
		latitude: result.latitude,
		longitude: result.longitude,
		provider: result.provider,
		providerPlaceId: result.providerPlaceId,
		source: result.source,
	}
}

export async function fetchPlaces(params?: PlacesQueryParams): Promise<Place[]> {
	const { data } = await axios.get(apiUrl('/places'), { params })
	return data.places ?? []
}

/**
 * Places list mapped for NcSelect / option pickers.
 * @param queryParams
 */
export async function fetchPlacesOptions(
	queryParams: PlacesQueryParams = {},
): Promise<SelectOption[]> {
	const places = await fetchPlaces(queryParams)
	return places.map(toPlaceOption)
}

export async function lookupPlaces(
	queryParams: PlacesQueryParams = {},
): Promise<{ provider: string, results: PlaceLookupOption[] }> {
	const { data } = await axios.get(apiUrl('/places/lookup'), { params: queryParams })
	return {
		provider: data.provider ?? 'none',
		results: (data.results ?? []).map(toLookupOption),
	}
}

export async function reverseGeocodePlace(
	latitude: string,
	longitude: string,
): Promise<PlaceLookupOption | null> {
	const { data } = await axios.get(apiUrl('/places/reverse'), {
		params: { latitude, longitude },
	})
	return data.result ? toLookupOption(data.result) : null
}

export async function resolvePlace(payload: {
	id?: number | null
	name: string
	fullName?: string | null
	latitude?: string | null
	longitude?: string | null
	provider?: string | null
	providerPlaceId?: string | null
}): Promise<Place> {
	const { data } = await axios.post(apiUrl('/places/resolve'), { data: payload })
	return data
}

export async function createPlace(payload: {
	name: string
	fullName?: string | null
	latitude?: string | null
	longitude?: string | null
	provider?: string | null
	providerPlaceId?: string | null
}): Promise<Place> {
	const { data } = await axios.post(apiUrl('/places'), { data: payload })
	return data
}
