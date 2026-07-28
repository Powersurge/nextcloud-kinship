<script setup lang="ts">
import TimelineClock from 'vue-material-design-icons/TimelineClock.vue'
import { NcAppSidebarTab, NcButton, NcEmptyContent } from '@nextcloud/vue'
import TimelineItem from '@/components/TimelineItem.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import { computed } from 'vue'
import { createEmptyGenealogyDate } from '@/utils/date.ts'
import { storeToRefs } from 'pinia'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import { useEventStore } from '@/stores/use-event-store.ts'

const { selectedPerson } = storeToRefs(usePeopleStore())
const { openEventForm } = useEventStore()

const timelineEvents = computed(() => {
	const events = selectedPerson.value?.events ?? []
	return [...events].sort((a, b) => {
		const ay = a.date?.start?.year ?? 9999
		const by = b.date?.start?.year ?? 9999
		if (ay !== by) {
			return ay - by
		}
		const am = a.date?.start?.month ?? 0
		const bm = b.date?.start?.month ?? 0
		if (am !== bm) {
			return am - bm
		}
		const ad = a.date?.start?.day ?? 0
		const bd = b.date?.start?.day ?? 0
		return ad - bd
	})
})

function openCreateEventModal() {
	if (!selectedPerson.value) {
		return
	}

	openEventForm({
		type: 'custom',
		title: null,
		description: null,
		date: createEmptyGenealogyDate(),
		placeId: null,
		subjectId: selectedPerson.value.id,
	})
}
</script>

<template>
	<NcAppSidebarTab
		id="timeline"
		name="Timeline">
		<template #icon>
			<TimelineClock :size="20" />
		</template>

		<div class="timeline-tab">
			<div class="timeline-tab__header">
				<NcButton size="small" @click="openCreateEventModal">
					<template #icon>
						<Plus :size="16" />
					</template>
					Add event
				</NcButton>
			</div>

			<NcEmptyContent
				v-if="timelineEvents.length === 0"
				class="timeline-tab__empty"
				name="No events yet"
				description="Add birth, death, or other life events for this person.">
				<template #icon>
					<TimelineClock :size="20" />
				</template>
				<template #action>
					<NcButton variant="primary" @click="openCreateEventModal">
						<template #icon>
							<Plus :size="16" />
						</template>
						Add event
					</NcButton>
				</template>
			</NcEmptyContent>

			<div v-else class="timeline-tab__list">
				<TimelineItem
					v-for="(event, index) in timelineEvents"
					:key="event.id ?? index"
					:event="event"
					:is-last="index === timelineEvents.length - 1" />
			</div>
		</div>
	</NcAppSidebarTab>
</template>

<style scoped>
.timeline-tab {
	display: flex;
	flex-direction: column;
	gap: 1rem;
}

.timeline-tab__header {
	display: flex;
	align-items: center;
	justify-content: end;
}

.timeline-tab__list {
	padding-top: .25rem;
}

.timeline-tab__empty {
	margin-top: 1rem;
}
</style>
