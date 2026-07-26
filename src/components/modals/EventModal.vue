<script setup lang="ts">
import { computed, ref } from 'vue'
import { NcDialog, NcNoteCard } from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import EventForm from '../forms/EventForm.vue'

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

function close() {
	emit('update:open', false)
}

async function onSubmit(data: Record<string, unknown>) {
	saving.value = true
	error.value = ''

	try {
		let response
		if (isEdit.value) {
			response = await axios.put(
				generateUrl(`/apps/kinship/api/v1/events/${props.event!.id}`),
				{ data },
			)
		} else {
			response = await axios.post(
				generateUrl('/apps/kinship/api/v1/events'),
				{ data },
			)
		}

		emit('saved', response.data)
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
		:close-on-click-outside="!saving"
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
		</div>
	</NcDialog>
</template>

<style>
.event-dialog-content {
	padding: 10px 0px;
}
</style>
