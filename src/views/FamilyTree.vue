<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { NcEmptyContent, NcLoadingIcon } from '@nextcloud/vue'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import FamilyTreeIcon from 'vue-material-design-icons/FamilyTree.vue'
import FamilyTreeNode from '../components/FamilyTree.vue'

const root = ref<Record<string, any> | null>(null)
const loading = ref(true)

async function loadTree() {
	loading.value = true
	try {
		const response = await axios.get(
			generateUrl('/apps/kinship/api/v1/tree/1'),
		)
		root.value = response.data && response.data.id ? response.data : null
	} catch (e) {
		console.error('Failed to load family tree', e)
		root.value = null
	} finally {
		loading.value = false
	}
}

onMounted(loadTree)
</script>

<template>
	<div class="tree-view">
		<div class="tree-view__header">
			<h2 class="tree-view__title">
				Family tree
			</h2>
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

.tree-view__loading {
	margin-top: 32px;
}

.tree-view__content {
	margin: 16px;
	overflow-x: auto;
}
</style>
