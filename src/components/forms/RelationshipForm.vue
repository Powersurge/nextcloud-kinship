<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import {
	NcButton,
	NcNoteCard,
	NcTextArea,
} from '@nextcloud/vue'
import {
	isPartnershipType,
	isParenthoodInputType,
	isParenthoodStoredType,
	parentChildIdsFromInput,
} from '@/utils/relationships.ts'
import SelectPerson from '@/components/forms/SelectPerson.vue'
import SelectRelationshipType from '@/components/forms/SelectRelationshipType.vue'
import SelectCoParent from '@/components/forms/SelectCoParent.vue'

interface RelationshipFormState {
	personA: number | null
	personB: number | null
	type: string
	coParentId: number | null
	note: string
}

const props = defineProps<{
	/** Existing relationship to edit. When null the form is in create mode. */
	relationship?: Record<string, any> | null
	/** Disables inputs while a save request is in flight. */
	saving?: boolean
	/** Pre-fill Person A when creating from a person context. */
	defaultPersonA?: number | null
	/** Pre-fill relationship type when creating from a person context. */
	defaultType?: string | null
}>()

const emit = defineEmits<{
	submit: [value: Record<string, unknown>]
	cancel: []
}>()

const isEdit = () => Boolean(props.relationship?.id)

const form = reactive<RelationshipFormState>(emptyForm())

const isPartnership = computed(() => isPartnershipType(form.type))

const partnershipOnly = computed(() =>
	isEdit() && isPartnershipType(props.relationship?.type ?? ''),
)

const parentPersonId = computed(() => {
	if (isEdit() && isParenthoodStoredType(props.relationship?.type ?? '')) {
		return props.relationship?.personA ?? null
	}

	return parentChildIdsFromInput(form.personA, form.personB, form.type).parentId
})

function emptyForm(): RelationshipFormState {
	return {
		personA: null,
		personB: null,
		type: 'parent',
		coParentId: null,
		note: '',
	}
}

watch(
	() => [props.relationship, props.defaultPersonA, props.defaultType] as const,
	([relationship, defaultPersonA, defaultType]) => {
		Object.assign(form, emptyForm())
		if (relationship) {
			form.personA = relationship.personA ?? null
			form.personB = relationship.personB ?? null
			form.type = relationship.type || 'parent'
			form.note = relationship.note ?? ''
			return
		}

		if (defaultPersonA != null) {
			form.personA = defaultPersonA
		}
		if (defaultType) {
			form.type = defaultType
		}
	},
	{ immediate: true, deep: true },
)

watch(
	() => [form.type, form.personA, form.personB] as const,
	() => {
		if (!isEdit()) {
			form.coParentId = null
		}
	},
)

function onSubmit() {
	emit('submit', form)
}
</script>

<template>
	<form class="rel-form" @submit.prevent="onSubmit">
		<NcNoteCard type="info">
			<template v-if="isPartnership">
				Partnership links are symmetric: Person A and Person B are partners
				with the selected status.
			</template>
			<template v-else>
				Reads as "Person A is the {{ '{type}' }} of Person B". Other relationships
				(siblings, grandparents, cousins, in-laws, ...) are computed automatically.
			</template>
		</NcNoteCard>

		<div class="flex gap-2">
			<SelectPerson
				v-model="form.personA"
				class="w-full"
				input-label="Person A"
				placeholder="Select person"
				:disabled="saving || isEdit()" />

			<SelectPerson
				v-model="form.personB"
				class="w-full"
				input-label="Person B"
				placeholder="Select person"
				:disabled="saving || isEdit()" />
		</div>

		<SelectRelationshipType
			v-model="form.type"
			class="col-span-2 min-w-auto"
			:partnership-only="partnershipOnly"
			:disabled="saving || (isEdit() && !isPartnershipType(props.relationship?.type ?? ''))" />

		<SelectCoParent
			v-if="isParenthoodInputType(form.type)"
			v-model="form.coParentId"
			class="w-full"
			:parent-id="parentPersonId"
			:disabled="saving" />

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

.rel-form__actions {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	margin-top: 8px;
}
</style>
