<script setup lang="ts">
import TimelineClock from 'vue-material-design-icons/TimelineClock.vue'
import { NcButton, NcEmptyContent, NcLoadingIcon } from '@nextcloud/vue'
import TimelineItem from '@/components/TimelineItem.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { createEmptyGenealogyDate, toGenealogyDate } from '@/utils/date.ts'
import { storeToRefs } from 'pinia'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import { usePersonTimelineStore } from '@/stores/use-person-timeline-store.ts'
import { useEventStore } from '@/stores/use-event-store.ts'
import { usePersonSidebarStore } from '@/stores/use-person-sidebar-store.ts'
import { deleteEvent as deleteEventApi } from '@/api/events.ts'
import type { Event } from '@/types'
import { showConfirmation, showError } from '@nextcloud/dialogs'

const peopleStore = usePeopleStore()
const { selectedPerson } = storeToRefs(peopleStore)
const timelineStore = usePersonTimelineStore()
const { events, loading, hasMore } = storeToRefs(timelineStore)
const { loadNextPage, removeEvent } = timelineStore
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

function openEditEvent(event: Event) {
	if (!event.id) {
		return
	}

	openEventForm({
		id: event.id,
		type: event.type,
		title: event.title ?? null,
		description: event.description ?? null,
		date: toGenealogyDate(event.date),
		placeId: event.placeId ?? null,
	})
}

async function onDeleteEvent(event: Event) {
	if (!event.id) {
		return
	}

	const confirmed = await showConfirmation({
		name: 'Are you sure?',
		text: 'This action will delete this event and cannot be undone.',
		labelReject: 'Cancel',
	})

	if (!confirmed) {
		return
	}

	try {
		await deleteEventApi(event.id)
		removeEvent(event.id)
		if (event.type === 'birth' || event.type === 'death') {
			await peopleStore.refreshSelectedPerson()
		}
	} catch (e) {
		showError('Failed to delete event')
	}
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

watch(
	() => events.value.map((event) => event.id).filter(Boolean).join(','),
	(ids, previous) => {
		if (ids && ids !== previous) {
			usePersonSidebarStore().reloadCitations()
		}
	},
)

onMounted(setupObserver)
onBeforeUnmount(() => observer?.disconnect())
</script>

<template>
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
				:is-last="index === events.length - 1 && !hasMore"
				@edit="openEditEvent(event)"
				@delete="onDeleteEvent(event)" />

			<div
				v-if="hasMore"
				ref="sentinel"
				class="timeline-tab__sentinel">
				<NcLoadingIcon v-if="loading" :size="20" />
			</div>
		</div>
	</div>
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
