<script setup lang="ts">
import './styles/app.css'
import { NcContent, NcAppContent } from '@nextcloud/vue'
import Navigation from './components/ui/Navigation.vue'
import EventModal from '@/components/modals/EventModal.vue'
import { storeToRefs } from 'pinia'
import { useEventStore } from '@/stores/use-event-store.ts'
import { usePeopleStore } from '@/stores/use-people-store.ts'

const { showFormModal, eventForm } = storeToRefs(useEventStore())
const { refreshSelectedPerson } = usePeopleStore()

async function onEventSaved() {
	await refreshSelectedPerson()
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
	</NcContent>
</template>
