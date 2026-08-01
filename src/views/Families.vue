<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { NcButton, NcEmptyContent, NcLoadingIcon } from '@nextcloud/vue'
import HumanMaleFemaleChild from 'vue-material-design-icons/HumanMaleFemaleChild.vue'
import FamilyCard from '../components/FamilyCard.vue'
import FamilyModal from '../components/modals/FamilyModal.vue'
import { fetchFamilies } from '@/api/families.ts'

const families = ref<Array<Record<string, any>>>([])
const loading = ref(true)

const modalOpen = ref(false)
const editing = ref<Record<string, any> | null>(null)

async function loadFamilies() {
	loading.value = true
	try {
		families.value = await fetchFamilies()
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

onMounted(loadFamilies)
</script>

<template>
	<div class="families">
		<div class="families__header">
			<h2 class="families__title">
				Families
			</h2>
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
				@edit="openEdit(family)" />
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

.families__title {
	margin: 0;
}
</style>
