import { LIVING_STATUSES, type LivingStatus } from '@/constants.ts'

export const getLivingStatusLabel = (value: LivingStatus) => {
	return LIVING_STATUSES[value] ?? LIVING_STATUSES.unknown
}
