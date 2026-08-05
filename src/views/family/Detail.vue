<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { NcButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import EntityDetailLayout from '@/components/detail/EntityDetailLayout.vue'
import FamilyMembersTree from '@/components/families/FamilyMembersTree.vue'
import FamilyModal from '@/components/modals/FamilyModal.vue'
import MediaSection from '@/components/media/MediaSection.vue'
import { useFamilyDetailStore } from '@/stores/use-family-detail-store.ts'
import { usePermissionsStore } from '@/stores/use-permissions-store.ts'

const props = defineProps<{
	id: string | number
}>()

const familyStore = useFamilyDetailStore()
const { family, loading, error } = storeToRefs(familyStore)
const { canEdit } = storeToRefs(usePermissionsStore())

const activeTab = ref('overview')
const modalOpen = ref(false)

const familyId = computed(() => {
	const value = Number(props.id)
	return Number.isFinite(value) && value > 0 ? value : null
})

const typeLabels: Record<string, string> = {
	partnership: 'Partnership',
	single_parent: 'Single parent',
}

const title = computed(() => (family.value?.name as string) || 'Family')
const subtitle = computed(() => {
	const type = family.value?.type as string | undefined
	if (!type) {
		return family.value?.isDerived ? 'Auto-generated' : ''
	}
	const label = typeLabels[type] ?? type
	return family.value?.isDerived ? `${label} · Auto-generated` : label
})

const tabs = [
	{ id: 'overview', label: 'Overview' },
	{ id: 'members', label: 'Members' },
	{ id: 'media', label: 'Media' },
]

watch(
	familyId,
	(id) => {
		activeTab.value = 'overview'
		if (id) {
			familyStore.load(id)
		} else {
			familyStore.reset()
		}
	},
	{ immediate: true },
)

onBeforeUnmount(() => {
	familyStore.reset()
})

function openEdit() {
	if (family.value) {
		modalOpen.value = true
	}
}

async function onSaved() {
	if (familyId.value) {
		await familyStore.load(familyId.value)
	}
}
</script>

<template>
	<EntityDetailLayout
		:title="title"
		:subtitle="subtitle"
		back-to="/families"
		back-label="Back to families"
		:tabs="tabs"
		:active-tab="activeTab"
		:loading="loading && !family"
		:error="!familyId ? 'Invalid family id.' : error"
		@update:active-tab="activeTab = $event">
		<template #actions>
			<NcButton
				v-if="family && canEdit"
				variant="primary"
				@click="openEdit">
				<template #icon>
					<Pencil :size="20" />
				</template>
				Edit
			</NcButton>
		</template>

		<div v-if="activeTab === 'overview' && family" class="family-overview">
			<div class="family-overview__row">
				<span class="family-overview__label">Name</span>
				<span class="family-overview__value">{{ family.name || 'Unnamed family' }}</span>
			</div>
			<div class="family-overview__row">
				<span class="family-overview__label">Type</span>
				<span class="family-overview__value">
					{{ typeLabels[String(family.type)] || family.type || 'Unknown' }}
				</span>
			</div>
			<div class="family-overview__row">
				<span class="family-overview__label">Description</span>
				<span
					v-if="family.description"
					class="family-overview__value family-overview__value--pre">
					{{ family.description }}
				</span>
				<span v-else class="family-overview__empty">
					No description yet
				</span>
			</div>
		</div>

		<FamilyMembersTree v-else-if="activeTab === 'members'" />

		<MediaSection
			v-else-if="activeTab === 'media' && familyId"
			subject-type="family"
			:subject-id="familyId"
			:show-primary-action="false" />
	</EntityDetailLayout>

	<FamilyModal
		v-model:open="modalOpen"
		:family="family"
		@saved="onSaved" />
</template>

<style scoped>
.family-overview {
	display: flex;
	flex-direction: column;
	gap: 1.1rem;
	max-width: 42rem;
}

.family-overview__row {
	display: flex;
	flex-direction: column;
	gap: 0.25rem;
}

.family-overview__label {
	font-weight: 700;
}

.family-overview__value {
	color: var(--color-main-text);
	line-height: 1.5;
}

.family-overview__value--pre {
	white-space: pre-wrap;
}

.family-overview__empty {
	color: var(--color-text-maxcontrast);
	font-style: italic;
	font-size: 0.95rem;
}
</style>
