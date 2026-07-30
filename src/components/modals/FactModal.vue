<script setup lang="ts">
import { computed, ref } from 'vue'
import { NcDialog, NcNoteCard } from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import FactForm from '../forms/FactForm.vue'

const props = defineProps<{
	open: boolean
	fact?: Record<string, unknown> | null
}>()

const emit = defineEmits<{
	'update:open': [value: boolean]
	saved: [fact: Record<string, unknown>]
}>()

const saving = ref(false)
const error = ref('')

const isEdit = computed(() => Boolean(props.fact?.id))
const title = computed(() => (isEdit.value ? 'Edit fact' : 'New fact'))

function close() {
	emit('update:open', false)
}

async function onSubmit(data: Record<string, unknown>) {
	saving.value = true
	error.value = ''

	const payload = {
		...data,
		subjectType: props.fact?.subjectType ?? 'person',
		subjectId: props.fact?.subjectId,
	}

	try {
		let response
		if (isEdit.value) {
			response = await axios.put(
				generateUrl(`/apps/kinship/api/v1/facts/${props.fact!.id}`),
				{ data: payload },
			)
		} else {
			response = await axios.post(
				generateUrl('/apps/kinship/api/v1/facts'),
				{ data: payload },
			)
		}

		emit('saved', response.data)
		close()
	} catch (e) {
		error.value = 'Could not save the fact. Please try again.'
		console.error('Failed to save fact', e)
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

		<div class="fact-dialog-content">
			<FactForm
				:fact="fact"
				:saving="saving"
				@submit="onSubmit"
				@cancel="close" />
		</div>
	</NcDialog>
</template>

<style>
.fact-dialog-content {
	padding: 10px 0px;
}
</style>
