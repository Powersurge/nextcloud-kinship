<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { NcEmptyContent, NcLoadingIcon } from '@nextcloud/vue'
import HumanMaleFemaleChild from 'vue-material-design-icons/HumanMaleFemaleChild.vue'
import FamilyCard from '@/components/FamilyCard.vue'
import FamilyModal from '@/components/modals/FamilyModal.vue'
import { fetchFamilies } from '@/api/families.ts'
import { usePermissionsStore } from '@/stores/use-permissions-store.ts'

const router = useRouter()
const families = ref<Array<Record<string, any>>>([])
const loading = ref(true)
const { canEdit } = storeToRefs(usePermissionsStore())

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

function openFamily(family: Record<string, any>) {
	if (!family?.id) {
		return
	}
	router.push({ name: 'family-detail', params: { id: String(family.id) } })
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
			description="Families are auto-generated. Add relations among people to see them appear here.">
			<template #icon>
				<HumanMaleFemaleChild />
			</template>
		</NcEmptyContent>

		<div v-else class="families__list">
			<FamilyCard
				v-for="family in families"
				:key="family.id"
				:family="family"
				:can-edit="canEdit"
				@click="openFamily(family)"
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
