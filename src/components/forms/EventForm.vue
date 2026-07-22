<script setup lang="ts">
import { reactive, watch } from 'vue'
import {
	NcButton,
	NcDateTimePicker,
	NcSelect,
	NcTextField,
} from '@nextcloud/vue'

interface EventFormData {
	title: string
	type: string
	eventDate: string | null
	location: string | null
}

const props = defineProps<{
	/** Existing event to edit. When null the form is in create mode. */
	event?: Partial<EventFormData> | null
	/** Disables inputs while a save request is in flight. */
	saving?: boolean
}>()

const emit = defineEmits<{
	submit: [value: EventFormData]
	cancel: []
}>()

const typeOptions = [
	{ value: 'birth', label: 'Birth' },
	{ value: 'death', label: 'Death' },
	{ value: 'marriage', label: 'Marriage' },
	{ value: 'education', label: 'Education' },
	{ value: 'military', label: 'Military' },
	{ value: 'custom', label: 'Custom' },
]

const form = reactive<EventFormData>(emptyForm())

function emptyForm(): EventFormData {
	return {
		title: '',
		type: 'birth',
		eventDate: null,
		location: '',
	}
}

/** Reset the form from the incoming event whenever it changes. */
watch(
	() => props.event,
	(event) => {
		Object.assign(form, emptyForm(), event ?? {})
	},
	{ immediate: true, deep: true },
)

// NcDateTimePicker works with Date objects; the API stores ISO date strings.
function toDate(value: string | null): Date | null {
	return value ? new Date(value) : null
}

function fromDate(value: Date | null): string | null {
	if (!value) {
		return null
	}
	const year = value.getFullYear()
	const month = String(value.getMonth() + 1).padStart(2, '0')
	const day = String(value.getDate()).padStart(2, '0')
	return `${year}-${month}-${day}`
}

function onSubmit() {
	emit('submit', { ...form })
}
</script>

<template>
	<form class="event-form" @submit.prevent="onSubmit">
		<NcTextField
			v-model="form.title"
			label="Title"
			:required="true"
			:disabled="saving" />

		<NcSelect
			v-model="form.type"
			input-label="Type"
			:options="typeOptions"
			:reduce="(option) => option.value"
			label="label"
			:clearable="false"
			:disabled="saving" />

		<div class="event-form__field">
			<label class="event-form__label">Date</label>
			<NcDateTimePicker
				:model-value="toDate(form.eventDate)"
				type="date"
				:append-to-body="true"
				:disabled="saving"
				@update:model-value="form.eventDate = fromDate($event)" />
		</div>

		<NcTextField
			v-model="form.location"
			label="Location"
			:disabled="saving" />

		<div class="event-form__actions">
			<NcButton
				type="button"
				:disabled="saving"
				@click="emit('cancel')">
				Cancel
			</NcButton>

			<NcButton
				type="submit"
				variant="primary"
				:disabled="saving">
				Save
			</NcButton>
		</div>
	</form>
</template>

<style scoped>
.event-form {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.event-form__field {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.event-form__label {
	font-size: 0.9em;
	color: var(--color-text-maxcontrast);
}

.event-form__actions {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	margin-top: 8px;
}
</style>
