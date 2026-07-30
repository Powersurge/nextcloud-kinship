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

const { showFormModal, eventForm } = storeToRefs(useEventStore())
const { showFormModal: showFactModal, factForm } = storeToRefs(useFactStore())
const { refreshSelectedPerson } = usePeopleStore()
const { loadFacts } = useFactStore()

async function onEventSaved() {
	await refreshSelectedPerson()
}

async function onFactSaved() {
	const personId = usePeopleStore().selectedPerson?.id
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
