<script setup lang="ts">
import './styles/app.css'
import { NcContent, NcAppContent } from '@nextcloud/vue'
import Navigation from './components/ui/Navigation.vue'
import EventModal from '@/components/modals/EventModal.vue'
import FactModal from '@/components/modals/FactModal.vue'
import { storeToRefs } from 'pinia'
import { useEventStore } from '@/stores/use-event-store.ts'
import { useFactStore } from '@/stores/use-fact-store.ts'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import { usePersonTimelineStore } from '@/stores/use-person-timeline-store.ts'
import { usePersonSidebarStore } from '@/stores/use-person-sidebar-store.ts'

const { showFormModal, eventForm } = storeToRefs(useEventStore())
const { showFormModal: showFactModal, factForm } = storeToRefs(useFactStore())
const { refreshSelectedPerson } = usePeopleStore()
const { reloadForPerson } = usePersonTimelineStore()
const { loadFacts } = useFactStore()

async function onEventSaved() {
	const personId = usePersonSidebarStore().selectedPersonId
	await refreshSelectedPerson()
	if (personId) {
		await reloadForPerson(personId)
	}
}

async function onFactSaved() {
	const personId = usePersonSidebarStore().selectedPersonId
	if (personId) {
		await loadFacts('person', personId)
	}
}
</script>

<template>
	<NcContent app-name="kinship-app-navigation" class="content-styleguidist">
		<Navigation />

		<NcAppContent>
			<RouterView />
		</NcAppContent>

		<RouterView name="sidebar" />

		<EventModal
			v-model:open="showFormModal"
			:event="eventForm"
			@saved="onEventSaved" />

		<FactModal
			v-model:open="showFactModal"
			:fact="factForm"
			@saved="onFactSaved" />
	</NcContent>
</template>
