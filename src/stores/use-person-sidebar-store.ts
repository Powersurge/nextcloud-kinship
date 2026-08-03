import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import { usePersonTimelineStore } from '@/stores/use-person-timeline-store.ts'
import { useFactStore } from '@/stores/use-fact-store.ts'
import { useRelationshipStore } from '@/stores/use-relationship-store.ts'
import { useCitationStore, type CitationSubject } from '@/stores/use-citation-store.ts'

export type SidebarTab = 'overview' | 'timeline' | 'family'

export const usePersonSidebarStore = defineStore('personSidebarStore', () => {
	const selectedPersonId = ref<number | null>(null)
	const activeTab = ref<SidebarTab>('overview')

	const isOpen = computed(() => selectedPersonId.value !== null)

	async function openPerson(person: Record<string, any>) {
		const id = person?.id
		if (!id) {
			return
		}

		selectedPersonId.value = id
		await onPersonOpened(id)
	}

	async function onPersonOpened(personId: number) {
		const peopleStore = usePeopleStore()
		const timelineStore = usePersonTimelineStore()
		const factStore = useFactStore()
		const relationshipStore = useRelationshipStore()
		const citationStore = useCitationStore()

		factStore.reset()
		timelineStore.reset()
		relationshipStore.reset()
		citationStore.reset()

		await Promise.all([
			peopleStore.loadPersonSummary(personId),
			timelineStore.loadInitial(personId),
			factStore.loadFacts('person', personId),
			relationshipStore.loadForPerson(personId),
		])

		await reloadCitations()
	}

	async function reloadCitations() {
		const personId = selectedPersonId.value
		if (!personId) {
			return
		}

		const peopleStore = usePeopleStore()
		const timelineStore = usePersonTimelineStore()
		const factStore = useFactStore()
		const relationshipStore = useRelationshipStore()
		const citationStore = useCitationStore()
		const person = peopleStore.selectedPerson
		const family = relationshipStore.family

		const subjects: CitationSubject[] = [
			{ subjectType: 'person', subjectId: personId },
		]

		if (person?.birthEventId) {
			subjects.push({ subjectType: 'event', subjectId: person.birthEventId })
		}
		if (person?.deathEventId) {
			subjects.push({ subjectType: 'event', subjectId: person.deathEventId })
		}

		for (const fact of factStore.facts) {
			if (fact.id) {
				subjects.push({ subjectType: 'fact', subjectId: fact.id })
			}
		}

		for (const event of timelineStore.events) {
			if (event.id) {
				subjects.push({ subjectType: 'event', subjectId: event.id })
			}
		}

		const relationships = [
			...family.activePartnerships,
			...family.endedPartnerships,
			...family.children,
			...family.parents,
		]
		for (const relationship of relationships) {
			if (relationship.id) {
				subjects.push({ subjectType: 'relationship', subjectId: relationship.id })
			}
		}

		await citationStore.loadForSubjects(subjects)
	}

	function close() {
		selectedPersonId.value = null
		activeTab.value = 'overview'

		usePeopleStore().clearSelectedPerson()
		usePersonTimelineStore().reset()
		useFactStore().reset()
		useRelationshipStore().reset()
		useCitationStore().reset()
	}

	function setActiveTab(tab: SidebarTab) {
		activeTab.value = tab
	}

	return {
		selectedPersonId,
		activeTab,
		isOpen,
		openPerson,
		close,
		setActiveTab,
		onPersonOpened,
		reloadCitations,
	}
})
