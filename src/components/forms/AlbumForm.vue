<script setup lang="ts">
import { reactive, watch } from 'vue'
import { NcButton, NcTextField, NcTextArea } from '@nextcloud/vue'
import type { FieldErrors } from '@/utils/api-errors.ts'

const props = defineProps<{
	album?: Record<string, unknown> | null
	saving?: boolean
	fieldErrors?: FieldErrors | null
}>()

const emit = defineEmits<{
	submit: [value: Record<string, unknown>]
	cancel: []
}>()

const form = reactive({
	title: '',
	description: '',
})

watch(
	() => props.album,
	(album) => {
		form.title = (album?.title as string) ?? ''
		form.description = (album?.description as string) ?? ''
	},
	{ immediate: true },
)

function fieldError(field: string): string | undefined {
	return props.fieldErrors?.[field]?.[0]
}

function onSubmit() {
	emit('submit', {
		title: form.title,
		description: form.description || null,
		coverMediaId: props.album?.coverMediaId ?? null,
	})
}
</script>

<template>
	<form class="album-form" @submit.prevent="onSubmit">
		<NcTextField
			v-model="form.title"
			label="Title"
			:error="Boolean(fieldError('title'))"
			:helper-text="fieldError('title')"
			:disabled="saving"
			required />

		<NcTextArea
			v-model="form.description"
			label="Description"
			:disabled="saving" />

		<div class="album-form__actions">
			<NcButton
				type="button"
				:disabled="saving"
				@click="emit('cancel')">
				Cancel
			</NcButton>
			<NcButton
				type="submit"
				variant="primary"
				:disabled="saving || !form.title.trim()">
				{{ saving ? 'Saving…' : 'Save' }}
			</NcButton>
		</div>
	</form>
</template>

<style scoped>
.album-form {
	display: flex;
	flex-direction: column;
	gap: 0.85rem;
}

.album-form__actions {
	display: flex;
	justify-content: flex-end;
	gap: 0.5rem;
	margin-top: 0.25rem;
}
</style>
