<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue'
import {
	NcButton,
	NcDateTimePicker,
	NcSelect,
	NcTextArea,
	NcTextField,
} from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { createEmptyGenologyDate, fromDate, toDateValue } from '../../utils/date'
import type { GenologyDateType } from '../../types'
import GenealogyDatePicker from '../ui/GenealogyDatePicker.vue'

interface EventFormState {
	type: string
	title: string
	description: string
	date: GenologyDateType
	placeId: number | null
	subjectId: number | null
}

const props = defineProps<{
	/** Existing event to edit. When null the form is in create mode. */
	event?: Record<string, any> | null
	/** Disables inputs while a save request is in flight. */
	saving?: boolean
}>()

const emit = defineEmits<{
	submit: [value: Record<string, unknown>]
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

const people = ref<Array<{ value: number, label: string }>>([])
const places = ref<Array<{ value: number, label: string }>>([])

const isEdit = () => Boolean(props.event?.id)

const form = reactive<EventFormState>(emptyForm())

function emptyForm(): EventFormState {
	return {
		type: 'birth',
		title: '',
		description: '',
		date: createEmptyGenologyDate(),
		placeId: null,
		subjectId: null,
	}
}

watch(
	() => props.event,
	(event) => {
		Object.assign(form, emptyForm())
		if (event) {
			form.type = event.type ?? 'birth'
			form.title = event.title ?? ''
			form.description = event.description ?? ''
			form.date = event.date ?? createEmptyGenologyDate()
			form.placeId = event.placeId ?? null
		}
	},
	{ immediate: true, deep: true },
)

async function loadOptions() {
	try {
		const [peopleRes, placesRes] = await Promise.all([
			axios.get(generateUrl('/apps/kinship/api/v1/people')),
			axios.get(generateUrl('/apps/kinship/api/v1/places')),
		])
		people.value = (peopleRes.data.people ?? []).map((p: Record<string, any>) => ({
			value: p.id,
			label: `${p.firstName ?? ''} ${p.lastName ?? ''}`.trim() || `#${p.id}`,
		}))
		places.value = (placesRes.data.places ?? []).map((pl: Record<string, any>) => ({
			value: pl.id,
			label: pl.fullName || pl.name || `#${pl.id}`,
		}))
	} catch (e) {
		console.error('Failed to load event options', e)
	}
}

onMounted(loadOptions)

function toDate(value: string | null): Date | null {
	return value ? new Date(value) : null
}

function onSubmit() {
	const payload: Record<string, unknown> = {
		type: form.type,
		title: form.title || null,
		description: form.description || null,
		date: form.date,
		placeId: form.placeId,
	}

	// Participants are only assigned when creating the event.
	if (!isEdit() && form.subjectId) {
		payload.participants = [{ personId: form.subjectId, role: 'subject' }]
	}

	emit('submit', payload)
}
</script>

<template>
	<form class="event-form" @submit.prevent="onSubmit">
		<NcSelect
			v-model="form.type"
			input-label="Type"
			:options="typeOptions"
			:reduce="(option) => option.value"
			label="label"
			:clearable="false"
			:disabled="saving" />

		<NcTextField
			v-model="form.title"
			label="Title (optional)"
			:disabled="saving" />

		<div class="event-form__field">
			<GenealogyDatePicker
				v-model="form.date"
				label="Date" />
		</div>

		<NcSelect
			v-if="!isEdit()"
			v-model="form.subjectId"
			input-label="Subject person"
			placeholder="Who is this event about?"
			:options="people"
			:reduce="(option) => option.value"
			label="label"
			:disabled="saving" />

		<NcSelect
			v-model="form.placeId"
			input-label="Place"
			placeholder="Select place"
			:options="places"
			:reduce="(option) => option.value"
			label="label"
			:disabled="saving" />

		<NcTextArea
			v-model="form.description"
			label="Description"
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
