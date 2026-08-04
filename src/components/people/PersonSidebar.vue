<script setup lang="ts">
import {
	NcAppSidebar,
} from '@nextcloud/vue'
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import { usePersonSidebarStore } from '@/stores/use-person-sidebar-store.ts'
import TimelineTab from '@/components/people/sidebar/TimelineTab.vue'
import OverviewTab from '@/components/people/sidebar/OverviewTab.vue'
import FamilyTab from '@/components/people/sidebar/FamilyTab.vue'
import MediaTab from '@/components/people/sidebar/MediaTab.vue'
import { getLivingStatusLabel } from '@/utils/helpers.ts'
import { mediaPreviewUrl } from '@/utils/mediaPreview.ts'
import { generateUrl } from '@nextcloud/router'

const sidebarStore = usePersonSidebarStore()
const { isOpen } = storeToRefs(sidebarStore)
const { selectedPerson } = storeToRefs(usePeopleStore())

const status = computed(() => getLivingStatusLabel(selectedPerson.value?.livingStatus))
const background = computed(() => {
	const fileId = selectedPerson.value?.photoFileId
	if (typeof fileId === 'number' && fileId > 0) {
		return mediaPreviewUrl(fileId, 600)
	}
	return selectedPerson.value?.avatar
		|| generateUrl(`/avatar/${selectedPerson.value?.firstName}/600?guestFallback=true?v=1&x=128&y=128`)
})
</script>

<template>
	<NcAppSidebar
		:open="isOpen"
		:name="selectedPerson?.fullName || 'Selected Person Name'"
		:subname="status"
		:background="background"
		no-toggle
		@close="sidebarStore.close()">
		<OverviewTab />

		<TimelineTab />

		<FamilyTab />

		<MediaTab />
	</NcAppSidebar>
</template>
