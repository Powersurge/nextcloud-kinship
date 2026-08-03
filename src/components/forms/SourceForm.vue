<script setup lang="ts">
import { reactive, watch } from 'vue'
import { NcButton, NcSelect, NcTextArea, NcTextField } from '@nextcloud/vue'
import { SOURCE_TYPE_OPTIONS } from '@/constants.ts'
import type { FieldErrors } from '@/utils/api-errors.ts'

interface SourceFormState {
	title: string
	author: string
	publication: string
	repository: string
	sourceType: string | null
	url: string
	notes: string
}

const props = defineProps<{
	source?: Record<string, any> | null
	saving?: boolean
	fieldErrors?: FieldErrors | null
}>()

const emit = defineEmits<{
	submit: [value: Record<string, unknown>]
	cancel: []
}>()

const form = reactive<SourceFormState>(emptyForm())

function emptyForm(): SourceFormState {
	return {
		title: '',
		author: '',
		publication: '',
		repository: '',
		sourceType: null,
		url: '',
		notes: '',
	}
}

function fieldError(field: string): string {
	return props.fieldErrors?.[field]?.[0] ?? ''
}

watch(
	() => props.source,
	(source) => {
		Object.assign(form, emptyForm())
		if (source) {
			form.title = source.title ?? ''
			form.author = source.author ?? ''
			form.publication = source.publication ?? ''
			form.repository = source.repository ?? ''
			form.sourceType = source.sourceType ?? null
			form.url = source.url ?? ''
			form.notes = source.notes ?? ''
		}
	},
	{ immediate: true, deep: true },
)

function onSubmit() {
	emit('submit', {
		title: form.title.trim(),
		author: form.author,
		publication: form.publication,
		repository: form.repository,
		sourceType: form.sourceType || null,
		url: form.url,
		notes: form.notes,
	})
}
</script>

<template>
	<form class="source-form" @submit.prevent="onSubmit">
		<NcTextField
			v-model="form.title"
			label="Title"
			:disabled="saving"
			:error="Boolean(fieldError('title'))"
			:helper-text="fieldError('title')" />

		<div class="source-form__field">
			<NcSelect
				v-model="form.sourceType"
				input-label="Type"
				:options="SOURCE_TYPE_OPTIONS"
				:reduce="(option) => option.value"
				label="label"
				:clearable="true"
				:disabled="saving" />
			<p v-if="fieldError('sourceType')" class="source-form__error">
				{{ fieldError('sourceType') }}
			</p>
		</div>

		<NcTextField
			v-model="form.author"
			label="Author"
			:disabled="saving"
			:error="Boolean(fieldError('author'))"
			:helper-text="fieldError('author')" />

		<NcTextField
			v-model="form.publication"
			label="Publication"
			:disabled="saving"
			:error="Boolean(fieldError('publication'))"
			:helper-text="fieldError('publication')" />

		<NcTextField
			v-model="form.repository"
			label="Repository"
			:disabled="saving"
			:error="Boolean(fieldError('repository'))"
			:helper-text="fieldError('repository')" />

		<NcTextField
			v-model="form.url"
			label="URL"
			type="url"
			:disabled="saving"
			:error="Boolean(fieldError('url'))"
			:helper-text="fieldError('url')" />

		<NcTextArea
			v-model="form.notes"
			label="Notes"
			:disabled="saving"
			:error="Boolean(fieldError('notes'))"
			:helper-text="fieldError('notes')" />

		<div class="source-form__actions">
			<NcButton type="button" :disabled="saving" @click="emit('cancel')">
				Cancel
			</NcButton>
			<NcButton type="submit" variant="primary" :disabled="saving">
				{{ saving ? 'Saving…' : 'Save' }}
			</NcButton>
		</div>
	</form>
</template>

<style scoped>
.source-form {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.source-form__field {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.source-form__error {
	margin: 0;
	font-size: 0.85rem;
	color: var(--color-text-error, var(--color-error));
}

.source-form__actions {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	margin-top: 8px;
}
</style>
