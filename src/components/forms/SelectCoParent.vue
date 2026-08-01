<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { NcSelect } from '@nextcloud/vue'
import { relationshipTypeLabel } from '@/utils/relationships.ts'
import { fetchPersonFamily } from '@/api/people.ts'

interface PartnerOption {
	value: number
	label: string
}

const model = defineModel<number | null>({ default: null })

const props = defineProps<{
	/** The parent person whose partners are offered as co-parents. */
	parentId?: number | null
	disabled?: boolean
}>()

const loading = ref(false)
const partnerOptions = ref<PartnerOption[]>([])

const options = computed(() => [
	{ value: null, label: 'None (single parent)' },
	...partnerOptions.value,
])

async function loadPartners(parentId: number) {
	loading.value = true
	try {
		const data = await fetchPersonFamily(parentId)

		const allPartners = [
			...(data.activePartnerships ?? []),
			...(data.endedPartnerships ?? []),
		]

		const partners = new Map<number, string>()

		for (const relationship of allPartners) {
			partners.set(
				relationship.relatedPersonId,
				`${relationship.relatedPersonName} (${relationshipTypeLabel(relationship.type)})`,
			)
		}

		partnerOptions.value = [...partners.entries()]
			.map(([value, label]) => ({ value, label }))
			.sort((a, b) => a.label.localeCompare(b.label))
	} catch (e) {
		console.error('Failed to load co-parent options', e)
		partnerOptions.value = []
	} finally {
		loading.value = false
	}
}

watch(
	() => props.parentId,
	(parentId) => {
		partnerOptions.value = []

		if (parentId != null) {
			loadPartners(parentId)
		}
	},
	{ immediate: true },
)

function reduceOption(option: PartnerOption | { value: null, label: string }) {
	return option.value
}
</script>

<template>
	<NcSelect
		v-model="model"
		input-label="Other parent (optional)"
		:placeholder="loading ? 'Loading partners…' : 'Select spouse / partner'"
		:options="options"
		:reduce="reduceOption"
		label="label"
		:clearable="false"
		:disabled="disabled || loading || parentId == null"
		v-bind="$attrs" />
</template>
