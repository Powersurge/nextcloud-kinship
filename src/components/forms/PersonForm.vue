<script setup lang="ts">
import { reactive, watch } from 'vue'
import {
	NcButton,
	NcCheckboxRadioSwitch,
	NcDateTimePicker,
	NcSelect,
	NcTextArea,
	NcTextField,
} from '@nextcloud/vue'
import { getFilePickerBuilder } from '@nextcloud/dialogs'

interface PersonFormData {
	firstName: string
	middleName: string
	lastName: string
	preferredName: string
	gender: string | null
	birthDate: string | null
	deathDate: string | null
	alive: boolean
	biography: string | null
}

const props = defineProps<{
	/** Existing person to edit. When null the form is in create mode. */
	person?: Partial<PersonFormData> | null
	/** Disables inputs while a save request is in flight. */
	saving?: boolean
}>()

const emit = defineEmits<{
	submit: [value: PersonFormData]
	cancel: []
}>()

const genderOptions = ['female', 'male', 'other', 'unknown']

const form = reactive<PersonFormData>(emptyForm())

function emptyForm(): PersonFormData {
	return {
		firstName: '',
		middleName: '',
		lastName: '',
		preferredName: '',
		gender: null,
		birthDate: null,
		deathDate: null,
		alive: true,
		biography: '',
	}
}

/** Reset the form from the incoming person whenever it changes. */
watch(
	() => props.person,
	(person) => {
		Object.assign(form, emptyForm(), person ?? {})
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

// eslint-disable-next-line no-unused-vars
async function showFilePicker() {
	const filepicker = getFilePickerBuilder('Pick plain text files')
		.addMimeTypeFilter('text/plain')
		.addButton({
			label: 'Pick',
			// eslint-disable-next-line no-console
			callback: (nodes) => console.log('Picked', nodes),
		})
		.build()

	// You get the file nodes by the button callback, but also the pick yields the paths of the picked files
	const paths = await filepicker.pick()

	// eslint-disable-next-line no-console
	console.log(paths)
}

function onSubmit() {
	emit('submit', { ...form })
}
</script>

<template>
	<form class="person-form" @submit.prevent="onSubmit">
		<div class="person-form__row">
			<NcTextField
				v-model="form.firstName"
				label="First name"
				:required="true"
				:disabled="saving" />

			<NcTextField
				v-model="form.middleName"
				label="Middle name"
				:disabled="saving" />
		</div>

		<div class="person-form__row">
			<NcTextField
				v-model="form.lastName"
				label="Last name"
				:required="true"
				:disabled="saving" />

			<NcTextField
				v-model="form.preferredName"
				label="Preferred name"
				:disabled="saving" />
		</div>

		<NcSelect
			v-model="form.gender"
			input-label="Gender"
			placeholder="Select gender"
			:options="genderOptions"
			:disabled="saving" />

		<div class="person-form__row">
			<div class="person-form__field">
				<label class="person-form__label">Birth date</label>
				<NcDateTimePicker
					:model-value="toDate(form.birthDate)"
					type="date"
					:append-to-body="true"
					:disabled="saving"
					@update:model-value="form.birthDate = fromDate($event)" />
			</div>

			<div class="person-form__field">
				<label class="person-form__label">Death date</label>
				<NcDateTimePicker
					:model-value="toDate(form.deathDate)"
					type="date"
					:append-to-body="true"
					:disabled="saving"
					@update:model-value="form.deathDate = fromDate($event)" />
			</div>
		</div>

		<NcCheckboxRadioSwitch
			v-model="form.alive"
			type="switch"
			:disabled="saving">
			Living
		</NcCheckboxRadioSwitch>

		<NcTextArea
			v-model="form.biography"
			label="Biography"
			:disabled="saving" />

		<div class="person-form__actions">
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
.person-form {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.person-form__row {
	display: flex;
	gap: 12px;
	flex-wrap: wrap;
}

.person-form__row > * {
	flex: 1 1 200px;
}

.person-form__field {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.person-form__label {
	font-size: 0.9em;
	color: var(--color-text-maxcontrast);
}

.person-form__actions {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	margin-top: 8px;
}
</style>
