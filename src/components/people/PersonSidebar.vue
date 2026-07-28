<script setup lang="ts">
import {
	NcAppSidebar,
	NcAppSidebarTab,
} from '@nextcloud/vue'
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import AccountGroup from 'vue-material-design-icons/AccountGroup.vue'
import { LIVING_STATUSES } from '@/constants.ts'
import TimelineTab from '@/components/people/sidebar/TimelineTab.vue'
import OverviewTab from '@/components/people/sidebar/OverviewTab.vue'

const { selectedPerson } = storeToRefs(usePeopleStore())

const open = computed(() => selectedPerson.value !== null)
const status = computed(() =>
	LIVING_STATUSES[selectedPerson.value?.livingStatus as keyof typeof LIVING_STATUSES]
	?? LIVING_STATUSES.unknown,
)
</script>

<template>
	<NcAppSidebar
		:open="open"
		:name="selectedPerson?.fullName"
		:subname="status"
		:background="selectedPerson?.avatar || 'https://placehold.co/600x300/e5e7eb/6b7280?text=Person'"
		no-toggle
		@close="selectedPerson = null">
		<OverviewTab />

		<TimelineTab />

		<NcAppSidebarTab
			id="family"
			:order="3"
			name="Family">
			<template #icon>
				<AccountGroup :size="20" />
			</template>
		</NcAppSidebarTab>
	</NcAppSidebar>
</template>
