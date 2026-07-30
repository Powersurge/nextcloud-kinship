<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { NcSelect } from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import {
	isPartnershipType,
	relationshipTypeLabel,
} from '@/utils/relationships.ts'

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
		const [relRes, peopleRes] = await Promise.all([
			axios.get(generateUrl(`/apps/kinship/api/v1/people/${parentId}/relationships`)),
			axios.get(generateUrl('/apps/kinship/api/v1/people')),
		])

		const names: Record<number, string> = {}
		for (const person of peopleRes.data.people ?? []) {
			names[person.id] = `${person.firstName ?? ''} ${person.lastName ?? ''}`.trim()
				|| `#${person.id}`
		}

		const partners = new Map<number, string>()
		for (const relationship of relRes.data.relationships ?? []) {
			if (!isPartnershipType(relationship.type)) {
				continue
			}

			const partnerId = relationship.personA === parentId
				? relationship.personB
				: relationship.personA

			if (partnerId === parentId) {
				continue
			}

			const status = relationshipTypeLabel(relationship.type)
			const name = names[partnerId] ?? `#${partnerId}`
			partners.set(partnerId, `${name} (${status})`)
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
