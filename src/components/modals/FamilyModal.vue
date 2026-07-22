<script setup lang="ts">
import { computed, ref } from 'vue'
import { NcDialog, NcNoteCard } from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import FamilyForm from '../forms/FamilyForm.vue'

const props = defineProps<{
	/** Controls visibility (use with v-model:open). */
	open: boolean
	/** Family being edited; null/undefined means create mode. */
	family?: Record<string, unknown> | null
}>()

const emit = defineEmits<{
	'update:open': [value: boolean]
	/** Emitted with the persisted family after a successful save. */
	saved: [family: Record<string, unknown>]
}>()

const saving = ref(false)
const error = ref('')

const isEdit = computed(() => Boolean(props.family?.id))
const title = computed(() => (isEdit.value ? 'Edit family' : 'New family'))

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
				generateUrl(`/apps/kinship/api/v1/families/${props.family!.id}`),
				{ data },
			)
		} else {
			response = await axios.post(
				generateUrl('/apps/kinship/api/v1/families'),
				{ data },
			)
		}

		emit('saved', response.data)
		close()
	} catch (e) {
		error.value = 'Could not save the family. Please try again.'
		console.error('Failed to save family', e)
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

		<div class="family-dialog-content">
			<FamilyForm
				:family="family"
				:saving="saving"
				@submit="onSubmit"
				@cancel="close" />
		</div>
	</NcDialog>
</template>

<style>
.family-dialog-content {
	padding: 10px 0px;
}
</style>
