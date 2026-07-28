<script setup lang="ts">
import './styles/app.css'
import { NcContent, NcAppContent } from '@nextcloud/vue'
import Navigation from './components/ui/Navigation.vue'
import EventModal from '@/components/modals/EventModal.vue'
import { storeToRefs } from 'pinia'
import { useEventStore } from '@/stores/use-event-store.ts'

const { showFormModal, eventForm } = storeToRefs(useEventStore())
</script>

<template>
	<NcContent app-name="kinship-app-navigation" class="content-styleguidist">
		<Navigation />

		<NcAppContent>
			<RouterView />
		</NcAppContent>

		<RouterView name="sidebar" />

		<EventModal
			:event="eventForm"
			:open="showFormModal"
			@close="showFormModal = false" />
	</NcContent>
</template>
