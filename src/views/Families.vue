<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { NcButton, NcEmptyContent, NcLoadingIcon } from '@nextcloud/vue'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import HumanMaleFemaleChild from 'vue-material-design-icons/HumanMaleFemaleChild.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import FamilyCard from '../components/FamilyCard.vue'
import FamilyModal from '../components/modals/FamilyModal.vue'

const families = ref<Array<Record<string, any>>>([])
const loading = ref(true)

const modalOpen = ref(false)
const editing = ref<Record<string, any> | null>(null)

async function loadFamilies() {
	loading.value = true
	try {
		const response = await axios.get(
			generateUrl('/apps/kinship/api/v1/families'),
		)
		families.value = response.data.families ?? []
	} catch (e) {
		console.error('Failed to load families', e)
	} finally {
		loading.value = false
	}
}

function openCreate() {
	editing.value = null
	modalOpen.value = true
}

function openEdit(family: Record<string, any>) {
	editing.value = family
	modalOpen.value = true
}

function onSaved() {
	loadFamilies()
}

async function deleteFamily(family: Record<string, any>) {
	if (!window.confirm(`Delete ${family.name || 'this family'}?`)) {
		return
	}
	try {
		await axios.delete(
			generateUrl(`/apps/kinship/api/v1/families/${family.id}`),
		)
		families.value = families.value.filter((f) => f.id !== family.id)
	} catch (e) {
		console.error('Failed to delete family', e)
	}
}

onMounted(loadFamilies)
</script>

<template>
	<div class="families">
		<div class="families__header">

		</div>

		<NcLoadingIcon v-if="loading" :size="32" class="families__loading" />

		<NcEmptyContent
			v-else-if="families.length === 0"
			name="No families yet"
			description="Add your first family to get started.">
			<template #icon>
				<HumanMaleFemaleChild />
			</template>
			<template #action>
				<NcButton variant="primary" @click="openCreate">
					New family
				</NcButton>
			</template>
		</NcEmptyContent>

		<div v-else class="families__list">
			<FamilyCard
				v-for="family in families"
				:key="family.id"
				:family="family"
				@edit="openEdit(family)"
				@delete="deleteFamily(family)" />
		</div>

		<FamilyModal
			v-model:open="modalOpen"
			:family="editing"
			@saved="onSaved" />
	</div>
</template>

<style scoped>
.families {
	display: flex;
	flex-direction: column;
}

.families__header {
	display: flex;
	gap: var(--default-grid-baseline);
	align-items: center;
	flex: 0 0;
	max-width: 100%;
	margin-block: var(--app-navigation-padding, 4px);
	margin-inline: calc(var(--default-clickable-area) + 2 * var(--app-navigation-padding, 4px)) var(--app-navigation-padding, 4px);
}

.families__loading {
	margin-top: 32px;
}

.families__list {
	display: flex;
	flex-direction: column;
	gap: 8px;
	margin: 16px;
}
</style>
