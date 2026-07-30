<script setup lang="ts">
import { reactive, watch } from 'vue'
import { NcButton, NcSelect, NcTextArea, NcTextField } from '@nextcloud/vue'
import { createEmptyGenealogyDate, toGenealogyDate } from '@/utils/date.ts'
import type { GenealogyDateType } from '@/types'
import { FACT_TYPE_OPTIONS } from '@/constants.ts'
import GenealogyDatePicker from '../ui/GenealogyDatePicker.vue'
import SelectPlace from '@/components/forms/SelectPlace.vue'

interface FactFormState {
	type: string
	value: string
	placeId: number | null
	date: GenealogyDateType
	note: string
}

const props = defineProps<{
	fact?: Record<string, any> | null
	saving?: boolean
}>()

const emit = defineEmits<{
	submit: [value: Record<string, unknown>]
	cancel: []
}>()

const form = reactive<FactFormState>(emptyForm())

function emptyForm(): FactFormState {
	return {
		type: 'occupation',
		value: '',
		placeId: null,
		date: createEmptyGenealogyDate(),
		note: '',
	}
}

watch(
	() => props.fact,
	(fact) => {
		Object.assign(form, emptyForm())
		if (fact) {
			form.type = fact.type ?? 'occupation'
			form.value = fact.value ?? ''
			form.placeId = fact.placeId ?? null
			form.date = toGenealogyDate(fact.date)
			form.note = fact.note ?? ''
		}
	},
	{ immediate: true, deep: true },
)

function onSubmit() {
	emit('submit', {
		type: form.type,
		value: form.value || null,
		placeId: form.placeId,
		date: form.date,
		note: form.note || null,
	})
}
</script>

<template>
	<form class="fact-form" @submit.prevent="onSubmit">
		<NcSelect
			v-model="form.type"
			input-label="Type"
			:options="FACT_TYPE_OPTIONS"
			:reduce="(option) => option.value"
			label="label"
			:clearable="false"
			:disabled="saving" />

		<NcTextArea
			v-model="form.value"
			:label="`Description of ${FACT_TYPE_OPTIONS.find((o) => o.value === form.type)?.label}`"
			required
			:disabled="saving" />

		<div class="fact-form__field">
			<GenealogyDatePicker
				v-model="form.date"
				label="Date" />
		</div>

		<SelectPlace
			v-model="form.placeId"
			input-label="Place"
			placeholder="Select place"
			:disabled="saving" />

		<NcTextArea
			v-model="form.note"
			label="Note"
			:disabled="saving" />

		<div class="fact-form__actions">
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
.fact-form {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.fact-form__field {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.fact-form__actions {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	margin-top: 8px;
}
</style>
