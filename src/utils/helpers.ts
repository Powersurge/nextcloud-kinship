import { LIVING_STATUSES, type LivingStatus } from '@/constants.ts'

export const getLivingStatusLabel = (value: LivingStatus) => {
	return LIVING_STATUSES[value] ?? LIVING_STATUSES.unknown
}

export const debounce = (callback: () => void, delayMS = 500) => {
	let timeoutId: ReturnType<typeof setTimeout> | null

	return () => {
		if (timeoutId) {
			clearTimeout(timeoutId)
		}

		timeoutId = setTimeout(() => {
			callback()
		}, delayMS)
	}
}
