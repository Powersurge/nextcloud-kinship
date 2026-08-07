<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { NcDialog, NcNoteCard } from '@nextcloud/vue'
import CitationForm from '../forms/CitationForm.vue'
import MediaSection from '@/components/media/MediaSection.vue'
import { createCitation, updateCitation } from '@/api/citations.ts'
import { getErrorMessage, getFieldErrors, type FieldErrors } from '@/utils/api-errors.ts'

const props = defineProps<{
	open: boolean
	citation?: Record<string, unknown> | null
}>()

const emit = defineEmits<{
	'update:open': [value: boolean]
	saved: [citation: Record<string, unknown>]
}>()

const saving = ref(false)
const error = ref('')
const fieldErrors = ref<FieldErrors | null>(null)

const isEdit = computed(() => Boolean(props.citation?.id))
const title = computed(() => (isEdit.value ? 'Edit citation' : 'Add citation'))
const citationId = computed(() => (typeof props.citation?.id === 'number' ? props.citation.id : null))

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

	const payload = {
		...data,
		subjectType: props.citation?.subjectType,
		subjectId: props.citation?.subjectId,
	}

	try {
		const citation = isEdit.value
			? await updateCitation(props.citation!.id as number, payload)
			: await createCitation(payload)

		emit('saved', citation)
		close()
	} catch (e) {
		const errors = getFieldErrors(e)
		if (errors) {
			fieldErrors.value = errors
			error.value = ''
		} else {
			error.value = getErrorMessage(e, 'Could not save the citation. Please try again.')
		}
		console.error('Failed to save citation', e)
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

		<div class="citation-dialog-content">
			<CitationForm
				:citation="citation"
				:saving="saving"
				:field-errors="fieldErrors"
				@submit="onSubmit"
				@cancel="close" />

			<MediaSection
				v-if="citationId"
				class="citation-dialog-media"
				subject-type="citation"
				:subject-id="citationId"
				:show-primary-action="false" />
		</div>
	</NcDialog>
</template>

<style>
.citation-dialog-content {
	padding: 10px 0;
}

.citation-dialog-media {
	margin-top: 1rem;
	padding-top: 0.75rem;
	border-top: 1px solid var(--color-border);
}
</style>
