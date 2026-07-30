<script setup lang="ts">
import { computed } from 'vue'
import { NcSelect } from '@nextcloud/vue'
import {
	RELATIONSHIP_GROUP_PARTNERSHIP,
	defaultTypeForGroup,
	relationshipGroupOptions,
	relationshipTypeGroup,
	relationshipTypesForGroup,
} from '@/utils/relationships.ts'
import type { RelationshipTypeOption } from '@/utils/relationships.ts'

const model = defineModel<string>({ default: 'parent' })

const props = defineProps<{
	disabled?: boolean
	/** When true, only show partnership status options. */
	partnershipOnly?: boolean
}>()

const selectedType = computed({
	get: () => model.value ?? 'parent',
	set: (value: string) => {
		model.value = value
	},
})

const selectedGroup = computed({
	get: () => relationshipTypeGroup(selectedType.value),
	set: (group: string) => {
		const types = relationshipTypesForGroup(group)
		if (!types.some((option) => option.value === selectedType.value)) {
			model.value = defaultTypeForGroup(group)
		}
	},
})

const typeOptions = computed(() => {
	if (props.partnershipOnly) {
		return relationshipTypesForGroup(RELATIONSHIP_GROUP_PARTNERSHIP)
	}
	return relationshipTypesForGroup(selectedGroup.value)
})

const isPartnership = computed(() =>
	props.partnershipOnly || selectedGroup.value === RELATIONSHIP_GROUP_PARTNERSHIP,
)

const typeInputLabel = computed(() =>
	isPartnership.value ? 'Partnership status' : 'Relationship (A is …)',
)

function reduceOption(option: RelationshipTypeOption): string {
	return option.value
}
</script>

<template>
	<div class="select-relationship-type">
		<NcSelect
			v-if="!partnershipOnly"
			v-model="selectedGroup"
			class="w-full"
			input-label="Relationship category"
			:options="relationshipGroupOptions"
			:reduce="reduceOption"
			label="label"
			:clearable="false"
			:disabled="disabled" />

		<NcSelect
			v-model="selectedType"
			class="w-full"
			:input-label="typeInputLabel"
			:options="typeOptions"
			:reduce="reduceOption"
			label="label"
			:clearable="false"
			:disabled="disabled"
			v-bind="$attrs" />
	</div>
</template>

<style scoped>
.select-relationship-type {
	display: flex;
	justify-content: space-between;
	gap: 8px;
}
</style>
