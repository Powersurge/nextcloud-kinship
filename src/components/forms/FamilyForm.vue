<script setup lang="ts">
import { reactive, watch } from 'vue'
import { NcButton, NcSelect, NcTextArea, NcTextField } from '@nextcloud/vue'

interface FamilyFormData {
	name: string
	type: string | null
	description: string | null
}

const props = defineProps<{
	/** Existing family to edit. When null the form is in create mode. */
	family?: Record<string, any> | null
	/** Disables inputs while a save request is in flight. */
	saving?: boolean
}>()

const emit = defineEmits<{
	submit: [value: FamilyFormData]
	cancel: []
}>()

const typeOptions = [
	{ value: 'partnership', label: 'Partnership' },
	{ value: 'single_parent', label: 'Single parent' },
]

const form = reactive<FamilyFormData>(emptyForm())

function emptyForm(): FamilyFormData {
	return {
		name: '',
		type: null,
		description: '',
	}
}

watch(
	() => props.family,
	(family) => {
		Object.assign(form, emptyForm())
		if (family) {
			form.name = family.name ?? ''
			form.type = family.type ?? null
			form.description = family.description ?? ''
		}
	},
	{ immediate: true, deep: true },
)

function onSubmit() {
	emit('submit', { ...form })
}
</script>

<template>
	<form class="family-form" @submit.prevent="onSubmit">
		<p class="family-form__hint">
			Members are derived automatically from relationships. Families hold
			shared information such as a name, notes and shared events.
		</p>

		<NcTextField
			v-model="form.name"
			label="Family name"
			:disabled="saving" />

		<NcSelect
			v-model="form.type"
			input-label="Type"
			placeholder="Select type"
			:options="typeOptions"
			:reduce="(option) => option.value"
			label="label"
			:disabled="saving" />

		<NcTextArea
			v-model="form.description"
			label="Notes"
			:disabled="saving" />

		<div class="family-form__actions">
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
.family-form {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.family-form__hint {
	margin: 0;
	font-size: 0.85em;
	color: var(--color-text-maxcontrast);
}

.family-form__actions {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	margin-top: 8px;
}
</style>
