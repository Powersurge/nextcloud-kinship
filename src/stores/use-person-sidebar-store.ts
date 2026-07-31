import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import { usePersonTimelineStore } from '@/stores/use-person-timeline-store.ts'
import { useFactStore } from '@/stores/use-fact-store.ts'
import { useRelationshipStore } from '@/stores/use-relationship-store.ts'

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

		factStore.reset()
		timelineStore.reset()
		relationshipStore.reset()

		await Promise.all([
			peopleStore.loadPersonSummary(personId),
			timelineStore.loadInitial(personId),
			factStore.loadFacts('person', personId),
			relationshipStore.loadForPerson(personId),
		])
	}

	function close() {
		selectedPersonId.value = null
		activeTab.value = 'overview'

		usePeopleStore().clearSelectedPerson()
		usePersonTimelineStore().reset()
		useFactStore().reset()
		useRelationshipStore().reset()
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
	}
})
