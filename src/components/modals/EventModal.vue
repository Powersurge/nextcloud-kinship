<script setup lang="ts">
import { computed, ref } from 'vue'
import { NcDialog, NcNoteCard } from '@nextcloud/vue'
import EventForm from '../forms/EventForm.vue'
import CitationList from '@/components/citations/CitationList.vue'
import MediaSection from '@/components/media/MediaSection.vue'
import { createEvent, updateEvent } from '@/api/events.ts'

const props = defineProps<{
	open: boolean
	event?: Record<string, unknown> | null
}>()

const emit = defineEmits<{
	'update:open': [value: boolean]
	/** Emitted with the persisted event after a successful save. */
	saved: [event: Record<string, unknown>]
}>()

const saving = ref(false)
const error = ref('')

const isEdit = computed(() => Boolean(props.event?.id))
const title = computed(() => (isEdit.value ? 'Edit event' : 'New event'))
const eventId = computed(() => (typeof props.event?.id === 'number' ? props.event.id : null))

function close() {
	emit('update:open', false)
}

async function onSubmit(data: Record<string, unknown>) {
	saving.value = true
	error.value = ''

	try {
		const event = isEdit.value
			? await updateEvent(props.event!.id as number, data)
			: await createEvent(data)

		emit('saved', event)
		close()
	} catch (e) {
		error.value = 'Could not save the event. Please try again.'
		console.error('Failed to save event', e)
	} finally {
		saving.value = false
	}
}
</script>

<template>
	<NcDialog
		:open="open"
		:name="title"
		size="normal"
		:close-on-click-outside="false"
		@update:open="emit('update:open', $event)">
		<NcNoteCard v-if="error" type="error">
			{{ error }}
		</NcNoteCard>

		<div class="event-dialog-content">
			<EventForm
				:event="event"
				:saving="saving"
				@submit="onSubmit"
				@cancel="close" />

			<CitationList
				v-if="eventId"
				class="event-dialog-citations"
				subject-type="event"
				:subject-id="eventId" />

			<MediaSection
				v-if="eventId"
				class="event-dialog-media"
				subject-type="event"
				:subject-id="eventId" />
		</div>
	</NcDialog>
</template>

<style>
.event-dialog-content {
	padding: 10px 0px;
}

.event-dialog-citations,
.event-dialog-media {
	margin-top: 1rem;
	padding-top: 0.75rem;
	border-top: 1px solid var(--color-border);
}
</style>
