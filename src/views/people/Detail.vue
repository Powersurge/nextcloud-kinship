<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { NcButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import EntityDetailLayout from '@/components/detail/EntityDetailLayout.vue'
import OverviewPanel from '@/components/people/panels/OverviewPanel.vue'
import TimelinePanel from '@/components/people/panels/TimelinePanel.vue'
import FamilyPanel from '@/components/people/panels/FamilyPanel.vue'
import MediaPanel from '@/components/people/panels/MediaPanel.vue'
import PersonModal from '@/components/modals/PersonModal.vue'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import { usePersonSidebarStore } from '@/stores/use-person-sidebar-store.ts'
import { getLivingStatusLabel } from '@/utils/helpers.ts'
import { mediaPreviewUrl } from '@/utils/mediaPreview.ts'
import { generateUrl } from '@nextcloud/router'

const props = defineProps<{
	id: string | number
}>()

const peopleStore = usePeopleStore()
const sidebarStore = usePersonSidebarStore()
const { selectedPerson } = storeToRefs(peopleStore)

const activeTab = ref('overview')
const loading = ref(false)
const error = ref<string | null>(null)
const modalOpen = ref(false)

const personId = computed(() => {
	const value = Number(props.id)
	return Number.isFinite(value) && value > 0 ? value : null
})

const title = computed(() => selectedPerson.value?.fullName || 'Person')
const subtitle = computed(() => getLivingStatusLabel(selectedPerson.value?.livingStatus))

const avatar = computed(() => {
	const person = selectedPerson.value
	if (!person) {
		return null
	}
	const fileId = person.photoFileId
	if (typeof fileId === 'number' && fileId > 0) {
		return mediaPreviewUrl(fileId, 1200)
	}
	return person.avatar
		|| generateUrl(`/avatar/${person.firstName}/1200?guestFallback=true`)
})

const tabs = [
	{ id: 'overview', label: 'Overview' },
	{ id: 'timeline', label: 'Timeline' },
	{ id: 'family', label: 'Family' },
	{ id: 'media', label: 'Media' },
]

async function loadPerson(id: number) {
	loading.value = true
	error.value = null
	try {
		await sidebarStore.openPerson({ id })
		if (!peopleStore.selectedPerson) {
			error.value = 'Person not found.'
		}
	} catch (e) {
		console.error('Failed to load person', e)
		error.value = 'Could not load this person.'
	} finally {
		loading.value = false
	}
}

watch(
	personId,
	(id) => {
		activeTab.value = 'overview'
		if (id) {
			loadPerson(id)
		} else {
			error.value = 'Invalid person id.'
		}
	},
	{ immediate: true },
)

function openEdit() {
	if (selectedPerson.value) {
		modalOpen.value = true
	}
}

async function onSaved() {
	if (personId.value) {
		await peopleStore.refreshSelectedPerson()
		await sidebarStore.reloadCitations()
	}
}
</script>

<template>
	<EntityDetailLayout
		:title="title"
		:subtitle="subtitle"
		back-to="/people"
		back-label="Back to people"
		:tabs="tabs"
		:active-tab="activeTab"
		:loading="loading"
		:error="error"
		:avatar-url="avatar"
		@update:active-tab="activeTab = $event">
		<template #actions>
			<NcButton
				v-if="selectedPerson"
				variant="primary"
				@click="openEdit">
				<template #icon>
					<Pencil :size="20" />
				</template>
				Edit person
			</NcButton>
		</template>

		<div class="person-detail-panel">
			<OverviewPanel v-if="activeTab === 'overview'" />
			<TimelinePanel v-else-if="activeTab === 'timeline'" />
			<FamilyPanel v-else-if="activeTab === 'family'" />
			<MediaPanel v-else-if="activeTab === 'media'" />
		</div>
	</EntityDetailLayout>

	<PersonModal
		v-model:open="modalOpen"
		:person="selectedPerson"
		@saved="onSaved" />
</template>

<style scoped>
.person-detail-panel {
	display: flex;
	flex-direction: column;
	gap: 1rem;
	min-width: 0;
}

.person-detail-panel :deep(.sidebar-sections) {
	gap: 1.75rem;
}

.person-detail-panel :deep(.facts-list),
.person-detail-panel :deep(.family-list),
.person-detail-panel :deep(.timeline-tab__list) {
	max-width: none;
}
</style>
