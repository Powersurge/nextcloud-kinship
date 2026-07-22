<script setup lang="ts">
import { reactive, watch } from 'vue'
import { NcButton, NcTextArea, NcTextField } from '@nextcloud/vue'

interface FamilyFormData {
	name: string
	description: string | null
}

const props = defineProps<{
	/** Existing family to edit. When null the form is in create mode. */
	family?: Partial<FamilyFormData> | null
	/** Disables inputs while a save request is in flight. */
	saving?: boolean
}>()

const emit = defineEmits<{
	submit: [value: FamilyFormData]
	cancel: []
}>()

const form = reactive<FamilyFormData>(emptyForm())

function emptyForm(): FamilyFormData {
	return {
		name: '',
		description: '',
	}
}

/** Reset the form from the incoming family whenever it changes. */
watch(
	() => props.family,
	(family) => {
		Object.assign(form, emptyForm(), family ?? {})
	},
	{ immediate: true, deep: true },
)

function onSubmit() {
	emit('submit', { ...form })
}
</script>

<template>
	<form class="family-form" @submit.prevent="onSubmit">
		<NcTextField
			v-model="form.name"
			label="Family name"
			:required="true"
			:disabled="saving" />

		<NcTextArea
			v-model="form.description"
			label="Description"
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

.family-form__actions {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	margin-top: 8px;
}
</style>
