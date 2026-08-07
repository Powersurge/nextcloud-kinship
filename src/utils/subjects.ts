import type { RouteLocationRaw } from 'vue-router'

const SUBJECT_LABELS: Record<string, string> = {
	person: 'Person',
	event: 'Event',
	fact: 'Fact',
	relationship: 'Relationship',
	family: 'Family',
}

/** Subject types that have their own detail route. */
const SUBJECT_ROUTES: Record<string, string> = {
	person: 'person-detail',
	event: 'event-detail',
	family: 'family-detail',
}

export function subjectLabel(subjectType: string): string {
	return SUBJECT_LABELS[subjectType] ?? subjectType
}

export function subjectRoute(subjectType: string, subjectId: number): RouteLocationRaw | null {
	const name = SUBJECT_ROUTES[subjectType]
	return name ? { name, params: { id: String(subjectId) } } : null
}
