import type { PlaceSummary } from '@/types'

export function placeDisplay(place?: PlaceSummary | null): string {
	if (!place) {
		return ''
	}

	return place.fullName || place.name || ''
}
