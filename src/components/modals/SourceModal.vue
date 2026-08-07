<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { NcDialog, NcNoteCard } from '@nextcloud/vue'
import SourceForm from '../forms/SourceForm.vue'
import MediaSection from '@/components/media/MediaSection.vue'
import { createSource, updateSource } from '@/api/sources.ts'
import type { Source } from '@/types'
import { getErrorMessage, getFieldErrors, type FieldErrors } from '@/utils/api-errors.ts'

const props = defineProps<{
	open: boolean
	source?: Record<string, unknown> | null
}>()

const emit = defineEmits<{
	'update:open': [value: boolean]
	saved: [source: Source]
}>()

const saving = ref(false)
const error = ref('')
const fieldErrors = ref<FieldErrors | null>(null)

const isEdit = computed(() => Boolean(props.source?.id))
const title = computed(() => (isEdit.value ? 'Edit source' : 'New source'))
const sourceId = computed(() => (typeof props.source?.id === 'number' ? props.source.id : null))

watch(
	() => props.open,
	(open) => {
		if (!open) {
			error.value = ''
			fieldErrors.value = null
		}
	},
)

function close() {
	emit('update:open', false)
}

async function onSubmit(data: Record<string, unknown>) {
	saving.value = true
	error.value = ''
	fieldErrors.value = null

	try {
		const source = isEdit.value
			? await updateSource(props.source!.id as number, data)
			: await createSource(data)

		emit('saved', source)
		close()
	} catch (e) {
		const errors = getFieldErrors(e)
		if (errors) {
			fieldErrors.value = errors
			error.value = ''
		} else {
			error.value = getErrorMessage(e, 'Could not save the source. Please try again.')
		}
		console.error('Failed to save source', e)
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

		<div class="source-dialog-content">
			<SourceForm
				:source="source"
				:saving="saving"
				:field-errors="fieldErrors"
				@submit="onSubmit"
				@cancel="close" />

			<MediaSection
				v-if="sourceId"
				class="source-dialog-media"
				subject-type="source"
				:subject-id="sourceId"
				:show-primary-action="false" />
		</div>
	</NcDialog>
</template>

<style>
.source-dialog-content {
	padding: 10px 0;
}

.source-dialog-media {
	margin-top: 1rem;
	padding-top: 0.75rem;
	border-top: 1px solid var(--color-border);
}
</style>
