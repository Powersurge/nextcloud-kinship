<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { NcEmptyContent, NcLoadingIcon, NcSelect } from '@nextcloud/vue'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import FamilyTreeIcon from 'vue-material-design-icons/FamilyTree.vue'
import FamilyTreeNode from '../components/FamilyTree.vue'

const root = ref<Record<string, any> | null>(null)
const loading = ref(true)
const people = ref<Array<{ value: number, label: string }>>([])
const rootId = ref<number | null>(null)

async function loadPeople() {
	try {
		const { data } = await axios.get(generateUrl('/apps/kinship/api/v1/people'))
		people.value = (data.people ?? []).map((p: Record<string, any>) => ({
			value: p.id,
			label: `${p.firstName ?? ''} ${p.lastName ?? ''}`.trim() || `#${p.id}`,
		}))
		if (people.value.length > 0 && rootId.value === null) {
			rootId.value = people.value[0].value
		}
	} catch (e) {
		console.error('Failed to load people', e)
	}
}

async function loadTree(personId: number) {
	loading.value = true
	try {
		const response = await axios.get(
			generateUrl(`/apps/kinship/api/v1/tree/${personId}`),
		)
		root.value = response.data && response.data.id ? response.data : null
	} catch (e) {
		console.error('Failed to load family tree', e)
		root.value = null
	} finally {
		loading.value = false
	}
}

watch(rootId, (id) => {
	if (id) {
		loadTree(id)
	} else {
		root.value = null
		loading.value = false
	}
})

onMounted(async () => {
	await loadPeople()
	if (rootId.value === null) {
		loading.value = false
	}
})
</script>

<template>
	<div class="tree-view">
		<div class="tree-view__header">
			<h2 class="tree-view__title">
				Family tree
			</h2>
		</div>

		<div class="tree-view__toolbar">
			<NcSelect
				v-model="rootId"
				input-label="Root person"
				placeholder="Select a person"
				:options="people"
				:reduce="(option) => option.value"
				label="label" />
		</div>

		<NcLoadingIcon v-if="loading" :size="32" class="tree-view__loading" />

		<NcEmptyContent
			v-else-if="!root"
			name="No tree to show"
			description="Add people and parent relationships to build a tree.">
			<template #icon>
				<FamilyTreeIcon />
			</template>
		</NcEmptyContent>

		<div v-else class="tree-view__content">
			<FamilyTreeNode :person="root" :children="root.children || []" />
		</div>
	</div>
</template>

<style scoped>
.tree-view {
	display: flex;
	flex-direction: column;
}

.tree-view__header {
	display: flex;
	gap: var(--default-grid-baseline);
	align-items: center;
	flex: 0 0;
	max-width: 100%;
	margin-block: var(--app-navigation-padding, 4px);
	margin-inline: calc(var(--default-clickable-area) + 2 * var(--app-navigation-padding, 4px)) var(--app-navigation-padding, 4px);
}

.tree-view__title {
	margin: 0;
}

.tree-view__toolbar {
	margin: 0 16px;
	max-width: 320px;
}

.tree-view__loading {
	margin-top: 32px;
}

.tree-view__content {
	margin: 16px;
	overflow-x: auto;
}
</style>
