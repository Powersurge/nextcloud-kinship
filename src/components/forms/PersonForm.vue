<script setup lang="ts">
import { reactive, watch } from 'vue'
import {
	NcButton,
	NcTextArea,
	NcTextField,
} from '@nextcloud/vue'
import GenealogyDatePicker from '../ui/GenealogyDatePicker.vue'
import type { GenealogyDateType } from '../../types'
import { createEmptyGenealogyDate, toGenealogyDate } from '../../utils/date.ts'
import SelectLivingStatus from './SelectLivingStatus.vue'
import SelectGender from './SelectGender.vue'

interface PersonFormData {
	firstName: string
	middleName: string
	lastName: string
	preferredName: string
	suffix: string
	gender: string | null
	livingStatus: string | null
	birthDate: GenealogyDateType
	deathDate: GenealogyDateType
	biography: string
}

const props = defineProps<{
	/** Existing person to edit. When null the form is in create mode. */
	person?: Record<string, any> | null
	/** Disables inputs while a save request is in flight. */
	saving?: boolean
}>()

const emit = defineEmits<{
	submit: [value: PersonFormData]
	cancel: []
}>()

const form = reactive<PersonFormData>(emptyForm())

function emptyForm(): PersonFormData {
	return {
		firstName: '',
		middleName: '',
		lastName: '',
		preferredName: '',
		suffix: '',
		gender: null,
		livingStatus: null,
		birthDate: createEmptyGenealogyDate(),
		deathDate: createEmptyGenealogyDate(),
		biography: '',
	}
}

/**
 * Reset the form from the incoming person whenever it changes. The person's
 * birth/death dates come from their birth/death events, so they arrive as API
 * date values and are converted to the editable shape.
 */
watch(
	() => props.person,
	(person) => {
		Object.assign(form, emptyForm())
		if (person) {
			form.firstName = person.firstName ?? ''
			form.middleName = person.middleName ?? ''
			form.lastName = person.lastName ?? ''
			form.preferredName = person.preferredName ?? ''
			form.suffix = person.suffix ?? ''
			form.gender = person.gender ?? null
			form.livingStatus = person.livingStatus ?? 'unknown'
			form.birthDate = toGenealogyDate(person.birthDate)
			form.deathDate = toGenealogyDate(person.deathDate)
			form.biography = person.biography ?? ''
		}

	},
	{ immediate: true, deep: true },
)

function onSubmit() {
	emit('submit', { ...form })
}
</script>

<template>
	<form class="grid grid-cols-2 gap-4" @submit.prevent="onSubmit">
		<div class="flex gap-4 justify-between col-span-full">
			<NcTextField
				v-model="form.firstName"
				label="First name"
				:required="true"
				:disabled="saving" />

			<NcTextField
				v-model="form.middleName"
				label="Middle name"
				:disabled="saving" />

			<NcTextField
				v-model="form.lastName"
				label="Last name"
				:required="true"
				:disabled="saving" />
		</div>

		<div class="flex gap-4 justify-between col-span-full">
			<NcTextField
				v-model="form.preferredName"
				label="Preferred name"
				:disabled="saving" />

			<NcTextField
				v-model="form.suffix"
				class="col-span-full"
				label="Suffix"
				:disabled="saving" />
		</div>

		<SelectGender
			v-model="form.gender"
			:disabled="saving" />

		<SelectLivingStatus
			v-model="form.livingStatus"
			:disabled="saving" />

		<div
			class="col-span-full flex flex-col gap-y-2">
			<GenealogyDatePicker
				v-model="form.birthDate"
				label="Birth Date" />

			<GenealogyDatePicker
				v-if="form.livingStatus === 'deceased'"
				v-model="form.deathDate"
				label="Death Date" />

			<p class="text-sm text-gray-500">
				Birth and death dates are stored as events; setting a death date marks the person deceased.
			</p>
		</div>

		<NcTextArea
			v-model="form.biography"
			class="col-span-full"
			label="Biography"
			:disabled="saving" />

		<div class="flex gap-4 justify-end col-span-full py-10">
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
