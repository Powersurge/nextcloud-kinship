<script setup lang="ts">
import TimelineClock from 'vue-material-design-icons/TimelineClock.vue'
import { NcAppSidebarTab, NcButton, NcEmptyContent, NcLoadingIcon } from '@nextcloud/vue'
import TimelineItem from '@/components/TimelineItem.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { createEmptyGenealogyDate } from '@/utils/date.ts'
import { storeToRefs } from 'pinia'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import { usePersonTimelineStore } from '@/stores/use-person-timeline-store.ts'
import { useEventStore } from '@/stores/use-event-store.ts'

const { selectedPerson } = storeToRefs(usePeopleStore())
const timelineStore = usePersonTimelineStore()
const { events, loading, hasMore } = storeToRefs(timelineStore)
const { loadNextPage } = timelineStore
const { openEventForm } = useEventStore()

const sentinel = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

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

function setupObserver() {
	observer?.disconnect()

	if (!sentinel.value) {
		return
	}

	observer = new IntersectionObserver(
		(entries) => {
			if (entries.some((entry) => entry.isIntersecting)) {
				loadNextPage()
			}
		},
		{ rootMargin: '120px' },
	)

	observer.observe(sentinel.value)
}

watch([sentinel, hasMore, events], () => {
	if (hasMore.value) {
		setupObserver()
	} else {
		observer?.disconnect()
	}
})

onMounted(setupObserver)
onBeforeUnmount(() => observer?.disconnect())
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

			<NcLoadingIcon v-if="loading && events.length === 0" :size="24" />

			<NcEmptyContent
				v-else-if="events.length === 0"
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
					v-for="(event, index) in events"
					:key="event.id ?? index"
					:event="event"
					:is-last="index === events.length - 1 && !hasMore" />

				<div
					v-if="hasMore"
					ref="sentinel"
					class="timeline-tab__sentinel">
					<NcLoadingIcon v-if="loading" :size="20" />
				</div>
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

.timeline-tab__sentinel {
	display: flex;
	justify-content: center;
	padding: 1rem 0;
	min-height: 48px;
}
</style>
