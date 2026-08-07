<script setup lang="ts">
import { computed } from 'vue'
import { NcActions, NcActionButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import MapMarkerOutline from 'vue-material-design-icons/MapMarkerOutline.vue'
import CalendarOutline from 'vue-material-design-icons/CalendarOutline.vue'
import { dateDisplay } from '@/utils/date.ts'
import { placeDisplay } from '@/utils/place.ts'
import { mediaPreviewUrl } from '@/utils/mediaPreview.ts'

const props = withDefaults(defineProps<{
	event: Record<string, any>
	canEdit?: boolean
	canDelete?: boolean
}>(), {
	canEdit: true,
	canDelete: true,
})

const emit = defineEmits<{
	edit: []
	delete: []
	click: []
}>()

const typeLabels: Record<string, string> = {
	birth: 'Birth',
	death: 'Death',
	marriage: 'Marriage',
	education: 'Education',
	military: 'Military',
	custom: 'Custom',
}

const typeLabel = computed(() => typeLabels[props.event.type] ?? props.event.type)
const heading = computed(() => props.event.title || typeLabel.value)
const when = computed(() => dateDisplay(props.event.date))
const where = computed(() => placeDisplay(props.event.place))
const coverUrl = computed(() => {
	const fileId = props.event.photoFileId
	return typeof fileId === 'number' && fileId > 0
		? mediaPreviewUrl(fileId, 168)
		: null
})
</script>

<template>
	<div
		class="event-card"
		:class="`event-card--${event.type || 'custom'}`"
		role="button"
		tabindex="0"
		@click="emit('click')"
		@keydown.enter.prevent="emit('click')">
		<div
			v-if="coverUrl"
			class="event-card__cover"
			aria-hidden="true">
			<img
				:src="coverUrl"
				:alt="heading"
				class="event-card__cover-img">
		</div>
		<div
			v-else
			class="event-card__accent"
			aria-hidden="true" />

		<div class="event-card__body">
			<div class="event-card__top">
				<span class="event-card__badge">{{ typeLabel }}</span>

				<NcActions v-if="canEdit || canDelete" class="event-card__actions" @click.stop>
					<NcActionButton v-if="canEdit" @click="emit('edit')">
						<template #icon>
							<Pencil :size="20" />
						</template>
						Edit
					</NcActionButton>
					<NcActionButton v-if="canDelete" @click="emit('delete')">
						<template #icon>
							<Delete :size="20" />
						</template>
						Delete
					</NcActionButton>
				</NcActions>
			</div>

			<h3 class="event-card__name">
				{{ heading }}
			</h3>

			<p v-if="when" class="event-card__meta">
				<CalendarOutline :size="14" class="event-card__meta-icon" />
				{{ when }}
			</p>

			<p v-if="where" class="event-card__meta">
				<MapMarkerOutline :size="14" class="event-card__meta-icon" />
				{{ where }}
			</p>
		</div>
	</div>
</template>

<style scoped>
.event-card {
	position: relative;
	display: flex;
	height: 100%;
	overflow: hidden;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large, 8px);
	background-color: var(--color-main-background);
	cursor: pointer;
	transition: border-color .15s, box-shadow .15s;
}

.event-card:hover,
.event-card:focus-visible {
	border-color: var(--color-primary-element);
	box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
	outline: none;
}

.event-card__accent {
	flex: 0 0 4px;
	background: var(--color-primary-element);
}

.event-card__cover {
	flex: 0 0 100px;
	align-self: stretch;
	overflow: hidden;
	background: var(--color-background-dark);
}

.event-card__cover-img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.event-card--birth .event-card__accent {
	background: #2ea043;
}

.event-card--death .event-card__accent {
	background: var(--color-text-maxcontrast);
}

.event-card--marriage .event-card__accent {
	background: #a371f7;
}

.event-card--education .event-card__accent {
	background: #1f6feb;
}

.event-card--military .event-card__accent {
	background: #9a6700;
}

.event-card__body {
	display: flex;
	flex: 1 1 auto;
	flex-direction: column;
	gap: 4px;
	min-width: 0;
	padding: 12px 14px;
}

.event-card__top {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 8px;
	margin-bottom: 2px;
}

.event-card__badge {
	display: inline-block;
	padding: 2px 8px;
	border-radius: var(--border-radius-pill);
	font-size: 0.72rem;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.03em;
	background: var(--color-background-dark);
	color: var(--color-text-maxcontrast);
}

.event-card__actions {
	margin: -6px -8px 0 0;
}

.event-card__name {
	margin: 0;
	font-size: 1rem;
	line-height: 1.35;
	overflow: hidden;
	display: -webkit-box;
	-webkit-box-orient: vertical;
	-webkit-line-clamp: 2;
}

.event-card__meta {
	display: flex;
	align-items: flex-start;
	gap: 0.3rem;
	margin: 0;
	color: var(--color-text-maxcontrast);
	font-size: 0.88em;
	line-height: 1.4;
}

.event-card__meta-icon {
	flex-shrink: 0;
	margin-top: 0.1rem;
	opacity: 0.85;
}
</style>
