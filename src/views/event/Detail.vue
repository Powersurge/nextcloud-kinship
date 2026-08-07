<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { NcButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import CalendarOutline from 'vue-material-design-icons/CalendarOutline.vue'
import MapMarkerOutline from 'vue-material-design-icons/MapMarkerOutline.vue'
import EntityDetailLayout from '@/components/detail/EntityDetailLayout.vue'
import EventModal from '@/components/modals/EventModal.vue'
import CitationList from '@/components/citations/CitationList.vue'
import MediaSection from '@/components/media/MediaSection.vue'
import { fetchEvent } from '@/api/events.ts'
import type { Event } from '@/types'
import { dateDisplay } from '@/utils/date.ts'
import { placeDisplay } from '@/utils/place.ts'
import { mediaPreviewUrl } from '@/utils/mediaPreview.ts'
import { useMediaStore } from '@/stores/use-media-store.ts'
import { usePermissionsStore } from '@/stores/use-permissions-store.ts'

const { canEdit } = storeToRefs(usePermissionsStore())

const props = defineProps<{
	id: string | number
}>()

const activeTab = ref('details')
const loading = ref(false)
const error = ref<string | null>(null)
const event = ref<Event | null>(null)
const modalOpen = ref(false)

const eventId = computed(() => {
	const value = Number(props.id)
	return Number.isFinite(value) && value > 0 ? value : null
})

const typeLabels: Record<string, string> = {
	birth: 'Birth',
	death: 'Death',
	marriage: 'Marriage',
	education: 'Education',
	military: 'Military',
	custom: 'Custom',
}

const typeLabel = computed(() => {
	const type = event.value?.type
	return type ? (typeLabels[type] ?? type) : ''
})

const title = computed(() => event.value?.title || typeLabel.value || 'Event')
const subtitle = computed(() => typeLabel.value)
const when = computed(() => dateDisplay(event.value?.date))
const where = computed(() => placeDisplay(event.value?.place))

const coverUrl = computed(() => {
	const fileId = event.value?.photoFileId
	return typeof fileId === 'number' && fileId > 0
		? mediaPreviewUrl(fileId, 1200)
		: undefined
})

function onPrimaryChanged(fileId: number | null) {
	if (!event.value) {
		return
	}
	event.value = { ...event.value, photoFileId: fileId }
}

const mediaStore = useMediaStore()
watch(
	() => {
		if (!eventId.value) {
			return null
		}
		return mediaStore.getMedia('event', eventId.value).find((item) => item.isPrimary)?.fileId ?? null
	},
	(fileId) => {
		if (!event.value) {
			return
		}
		const current = event.value.photoFileId ?? null
		if (current !== fileId) {
			event.value = { ...event.value, photoFileId: fileId }
		}
	},
)

const tabs = [
	{ id: 'details', label: 'Details' },
	{ id: 'citations', label: 'Citations' },
	{ id: 'media', label: 'Media' },
]

async function loadEvent(id: number) {
	loading.value = true
	error.value = null
	try {
		event.value = await fetchEvent(id)
	} catch (e) {
		console.error('Failed to load event', e)
		event.value = null
		error.value = 'Could not load this event.'
	} finally {
		loading.value = false
	}
}

watch(
	eventId,
	(id) => {
		activeTab.value = 'details'
		if (id) {
			loadEvent(id)
		} else {
			event.value = null
			error.value = 'Invalid event id.'
		}
	},
	{ immediate: true },
)

function openEdit() {
	if (event.value) {
		modalOpen.value = true
	}
}

async function onSaved(saved: Record<string, unknown>) {
	event.value = {
		...saved as Event,
		photoFileId: (saved as Event).photoFileId ?? event.value?.photoFileId ?? null,
	}
}
</script>

<template>
	<EntityDetailLayout
		:title="title"
		:subtitle="subtitle"
		:cover-url="coverUrl"
		back-to="/events"
		back-label="Back to events"
		:tabs="tabs"
		:active-tab="activeTab"
		:loading="loading && !event"
		:error="error"
		@update:active-tab="activeTab = $event">
		<template #actions>
			<NcButton
				v-if="event && canEdit"
				variant="primary"
				@click="openEdit">
				<template #icon>
					<Pencil :size="20" />
				</template>
				Edit
			</NcButton>
		</template>

		<div v-if="activeTab === 'details' && event" class="event-overview">
			<div class="event-overview__row">
				<span class="event-overview__label">Type</span>
				<span class="event-overview__value">{{ typeLabel }}</span>
			</div>
			<div class="event-overview__row">
				<span class="event-overview__label">Title</span>
				<span class="event-overview__value">{{ event.title || '—' }}</span>
			</div>
			<div class="event-overview__row">
				<span class="event-overview__label">Date</span>
				<span v-if="when" class="event-overview__value event-overview__meta">
					<CalendarOutline :size="16" />
					{{ when }}
				</span>
				<span v-else class="event-overview__empty">No date recorded</span>
			</div>
			<div class="event-overview__row">
				<span class="event-overview__label">Place</span>
				<span v-if="where" class="event-overview__value event-overview__meta">
					<MapMarkerOutline :size="16" />
					{{ where }}
				</span>
				<span v-else class="event-overview__empty">No place recorded</span>
			</div>
			<div class="event-overview__row">
				<span class="event-overview__label">Description</span>
				<span
					v-if="event.description"
					class="event-overview__value event-overview__value--pre">
					{{ event.description }}
				</span>
				<span v-else class="event-overview__empty">No description yet</span>
			</div>
		</div>

		<CitationList
			v-else-if="activeTab === 'citations' && eventId"
			subject-type="event"
			:subject-id="eventId" />

		<MediaSection
			v-else-if="activeTab === 'media' && eventId"
			subject-type="event"
			:subject-id="eventId"
			@primary-changed="onPrimaryChanged" />
	</EntityDetailLayout>

	<EventModal
		v-model:open="modalOpen"
		:event="event"
		@saved="onSaved" />
</template>

<style scoped>
.event-overview {
	display: flex;
	flex-direction: column;
	gap: 1.1rem;
	max-width: 42rem;
}

.event-overview__row {
	display: flex;
	flex-direction: column;
	gap: 0.25rem;
}

.event-overview__label {
	font-weight: 700;
}

.event-overview__value {
	color: var(--color-main-text);
	line-height: 1.5;
}

.event-overview__value--pre {
	white-space: pre-wrap;
}

.event-overview__meta {
	display: inline-flex;
	align-items: flex-start;
	gap: 0.35rem;
}

.event-overview__empty {
	color: var(--color-text-maxcontrast);
	font-style: italic;
	font-size: 0.95rem;
}
</style>
