<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { NcButton, NcEmptyContent, NcLoadingIcon } from '@nextcloud/vue'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import Calendar from 'vue-material-design-icons/Calendar.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import EventCard from '../components/EventCard.vue'
import EventModal from '../components/modals/EventModal.vue'

const events = ref<Array<Record<string, any>>>([])
const loading = ref(true)

const modalOpen = ref(false)
const editing = ref<Record<string, any> | null>(null)

async function loadEvents() {
	loading.value = true
	try {
		const response = await axios.get(
			generateUrl('/apps/kinship/api/v1/events'),
		)
		events.value = response.data.events ?? []
	} catch (e) {
		console.error('Failed to load events', e)
	} finally {
		loading.value = false
	}
}

function openCreate() {
	editing.value = null
	modalOpen.value = true
}

function openEdit(event: Record<string, any>) {
	editing.value = event
	modalOpen.value = true
}

function onSaved() {
	loadEvents()
}

async function deleteEvent(event: Record<string, any>) {
	if (!window.confirm(`Delete ${event.title || event.type || 'this event'}?`)) {
		return
	}
	try {
		await axios.delete(
			generateUrl(`/apps/kinship/api/v1/events/${event.id}`),
		)
		events.value = events.value.filter((e) => e.id !== event.id)
	} catch (e) {
		console.error('Failed to delete event', e)
	}
}

onMounted(loadEvents)
</script>

<template>
	<div class="events">
		<div class="events__header">
			<NcButton variant="primary" @click="openCreate">
				<template #icon>
					<Plus :size="20" />
				</template>
				New event
			</NcButton>
		</div>

		<NcLoadingIcon v-if="loading" :size="32" class="events__loading" />

		<NcEmptyContent
			v-else-if="events.length === 0"
			name="No events yet"
			description="Add your first event to get started.">
			<template #icon>
				<Calendar />
			</template>
			<template #action>
				<NcButton variant="primary" @click="openCreate">
					New event
				</NcButton>
			</template>
		</NcEmptyContent>

		<div v-else class="events__list">
			<EventCard
				v-for="event in events"
				:key="event.id"
				:event="event"
				@edit="openEdit(event)"
				@delete="deleteEvent(event)" />
		</div>

		<EventModal
			v-model:open="modalOpen"
			:event="editing"
			@saved="onSaved" />
	</div>
</template>

<style scoped>
.events {
	display: flex;
	flex-direction: column;
}

.events__header {
	display: flex;
	gap: var(--default-grid-baseline);
	align-items: center;
	flex: 0 0;
	max-width: 100%;
	margin-block: var(--app-navigation-padding, 4px);
	margin-inline: calc(var(--default-clickable-area) + 2 * var(--app-navigation-padding, 4px)) var(--app-navigation-padding, 4px);
}

.events__loading {
	margin-top: 32px;
}

.events__list {
	display: flex;
	flex-direction: column;
	gap: 8px;
	margin: 16px;
}
</style>
