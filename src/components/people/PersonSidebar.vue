<script setup lang="ts">
import {
	NcAppSidebar,
} from '@nextcloud/vue'
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import { usePersonSidebarStore } from '@/stores/use-person-sidebar-store.ts'
import { LIVING_STATUSES } from '@/constants.ts'
import TimelineTab from '@/components/people/sidebar/TimelineTab.vue'
import OverviewTab from '@/components/people/sidebar/OverviewTab.vue'
import FamilyTab from '@/components/people/sidebar/FamilyTab.vue'

const sidebarStore = usePersonSidebarStore()
const { isOpen } = storeToRefs(sidebarStore)
const { selectedPerson } = storeToRefs(usePeopleStore())

const status = computed(() =>
	LIVING_STATUSES[selectedPerson.value?.livingStatus as keyof typeof LIVING_STATUSES]
	?? LIVING_STATUSES.unknown,
)
</script>

<template>
	<NcAppSidebar
		:open="isOpen"
		:name="selectedPerson?.fullName || 'Selected Person Name'"
		:subname="status"
		:background="selectedPerson?.avatar || 'https://placehold.co/600x300/e5e7eb/6b7280?text=Person'"
		no-toggle
		@close="sidebarStore.close()">
		<OverviewTab />

		<TimelineTab />

		<FamilyTab />
	</NcAppSidebar>
</template>
