<script setup lang="ts">
import { computed, ref } from 'vue'
import { NcDialog, NcNoteCard } from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import RelationshipForm from '../forms/RelationshipForm.vue'

const props = defineProps<{
	/** Controls visibility (use with v-model:open). */
	open: boolean
	/** Relationship being edited; null/undefined means create mode. */
	relationship?: Record<string, any> | null
}>()

const emit = defineEmits<{
	'update:open': [value: boolean]
	saved: [relationship: Record<string, unknown>]
}>()

const saving = ref(false)
const error = ref('')

const isEdit = computed(() => Boolean(props.relationship?.id))
const title = computed(() => (isEdit.value ? 'Edit relationship' : 'New relationship'))

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
				generateUrl(`/apps/kinship/api/v1/relationships/${props.relationship!.id}`),
				{ data },
			)
		} else {
			response = await axios.post(
				generateUrl('/apps/kinship/api/v1/relationships'),
				{ data },
			)
		}

		emit('saved', response.data)
		close()
	} catch (e) {
		error.value = 'Could not save the relationship. Please try again.'
		console.error('Failed to save relationship', e)
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

		<div class="rel-dialog-content">
			<RelationshipForm
				:relationship="relationship"
				:saving="saving"
				@submit="onSubmit"
				@cancel="close" />
		</div>
	</NcDialog>
</template>

<style>
.rel-dialog-content {
	padding: 10px 0px;
}
</style>
