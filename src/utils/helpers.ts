import { LIVING_STATUSES, type LivingStatus } from '@/constants.ts'

export const getLivingStatusLabel = (value: LivingStatus) => {
	return LIVING_STATUSES[value] ?? LIVING_STATUSES.unknown
}

export const debounce = <TArgs extends unknown[]>(
	callback: (...args: TArgs) => void,
	delayMS = 500,
) => {
	let timeoutId: ReturnType<typeof setTimeout> | null = null

	return (...args: TArgs) => {
		if (timeoutId !== null) {
			clearTimeout(timeoutId)
		}

		timeoutId = setTimeout(() => {
			callback(...args)
		}, delayMS)
	}
}
