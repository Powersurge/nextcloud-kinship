<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue'
import {
	NcButton,
	NcDateTimePicker,
	NcNoteCard,
	NcSelect,
	NcTextArea,
} from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { createEmptyGenealogyDate, toDateValue, toGenealogyDate } from '@/utils/date.ts'
import { relationshipTypeOptions } from '@/utils/relationships.ts'
import type { GenealogyDateType } from '@/types'
import GenealogyDatePicker from '@/components/ui/GenealogyDatePicker.vue'

interface RelationshipFormState {
	personA: number | null
	personB: number | null
	type: string
	date: GenealogyDateType
	note: string
}

const props = defineProps<{
	/** Existing relationship to edit. When null the form is in create mode. */
	relationship?: Record<string, any> | null
	/** Disables inputs while a save request is in flight. */
	saving?: boolean
}>()

const emit = defineEmits<{
	submit: [value: Record<string, unknown>]
	cancel: []
}>()

const people = ref<Array<{ value: number, label: string }>>([])

const isEdit = () => Boolean(props.relationship?.id)

const form = reactive<RelationshipFormState>(emptyForm())

function emptyForm(): RelationshipFormState {
	return {
		personA: null,
		personB: null,
		type: 'parent',
		date: createEmptyGenealogyDate(),
		note: '',
	}
}

watch(
	() => props.relationship,
	(relationship) => {
		Object.assign(form, emptyForm())
		if (relationship) {
			form.personA = relationship.personA ?? null
			form.personB = relationship.personB ?? null
			form.type = relationship.type ?? 'parent'
			form.date = toGenealogyDate(relationship.date)
			form.note = relationship.note ?? ''
		}
	},
	{ immediate: true, deep: true },
)

async function loadPeople() {
	try {
		const { data } = await axios.get(generateUrl('/apps/kinship/api/v1/people'))
		people.value = (data.people ?? []).map((p: Record<string, any>) => ({
			value: p.id,
			label: `${p.firstName ?? ''} ${p.lastName ?? ''}`.trim() || `#${p.id}`,
		}))
	} catch (e) {
		console.error('Failed to load people', e)
	}
}

onMounted(loadPeople)

function toDate(value: string | null): Date | null {
	return value ? new Date(value) : null
}

function onSubmit() {
	if (isEdit()) {
		// The pair and type are fixed once stored; only date/note are editable.
		emit('submit', { date: toDateValue(form.date), note: form.note || null })
		return
	}

	emit('submit', {
		personA: form.personA,
		personB: form.personB,
		type: form.type,
		date: toDateValue(form.date),
		note: form.note || null,
	})
}
</script>

<template>
	<form class="rel-form" @submit.prevent="onSubmit">
		<NcNoteCard type="info">
			Reads as "Person A is the {{ '{type}' }} of Person B". Other relationships
			(siblings, grandparents, cousins, in-laws, ...) are computed automatically.
		</NcNoteCard>

		<NcSelect
			v-model="form.personA"
			input-label="Person A"
			placeholder="Select person"
			:options="people"
			:reduce="(option) => option.value"
			label="label"
			:disabled="saving || isEdit()" />

		<NcSelect
			v-model="form.type"
			input-label="Relationship (A is …)"
			:options="relationshipTypeOptions"
			:reduce="(option) => option.value"
			label="label"
			:clearable="false"
			:disabled="saving || isEdit()" />

		<NcSelect
			v-model="form.personB"
			input-label="Person B"
			placeholder="Select person"
			:options="people"
			:reduce="(option) => option.value"
			label="label"
			:disabled="saving || isEdit()" />

		<div class="rel-form__field">
			<GenealogyDatePicker
				v-model="form.date"
				label="Date (optional)" />
		</div>

		<NcTextArea
			v-model="form.note"
			label="Note"
			:disabled="saving" />

		<div class="rel-form__actions">
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
.rel-form {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.rel-form__field {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.rel-form__label {
	font-size: 0.9em;
	color: var(--color-text-maxcontrast);
}

.rel-form__actions {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	margin-top: 8px;
}
</style>
