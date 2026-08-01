<template>
	<div class="timeline-item" :class="{ 'timeline-item--last': isLast }">
		<div class="timeline-marker">
			<div class="timeline-dot" :class="`timeline-dot--${event.type}`" />
			<div v-if="!isLast" class="timeline-line" />
		</div>

		<div class="timeline-content">
			<div class="timeline-content__top">
				<div class="timeline-header">
					<span class="timeline-badge" :class="`timeline-badge--${event.type}`">
						{{ typeLabel }}
					</span>
					<span v-if="when" class="timeline-date">
						{{ when }}
					</span>
				</div>

				<NcActions v-if="showActions" class="timeline-actions">
					<NcActionButton @click="emit('edit')">
						<template #icon>
							<Pencil :size="20" />
						</template>
						Edit
					</NcActionButton>
					<NcActionButton @click="emit('delete')">
						<template #icon>
							<Delete :size="20" />
						</template>
						Delete
					</NcActionButton>
				</NcActions>
			</div>

			<h3 v-if="heading !== typeLabel" class="timeline-title">
				{{ heading }}
			</h3>

			<div v-if="where" class="timeline-meta">
				<MapMarkerOutline :size="14" class="timeline-meta__icon" />
				<span>{{ where }}</span>
			</div>

			<p
				v-if="event.description"
				class="timeline-description">
				{{ event.description }}
			</p>
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { NcActions, NcActionButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import MapMarkerOutline from 'vue-material-design-icons/MapMarkerOutline.vue'
import { dateDisplay } from '@/utils/date.ts'
import { placeDisplay } from '@/utils/place.ts'
import type { Event } from '@/types'

const props = withDefaults(defineProps<{
	event: Event
	isLast?: boolean
	showActions?: boolean
}>(), {
	isLast: false,
	showActions: true,
})

const emit = defineEmits<{
	edit: []
	delete: []
}>()

const TYPE_LABELS: Record<string, string> = {
	birth: 'Birth',
	death: 'Death',
	marriage: 'Marriage',
	education: 'Education',
	military: 'Military',
	custom: 'Custom',
}

const when = computed(() => dateDisplay(props.event.date))
const where = computed(() => placeDisplay(props.event.place))
const typeLabel = computed(() => TYPE_LABELS[props.event.type] ?? props.event.type)
const heading = computed(() => props.event.title || typeLabel.value)
</script>

<style scoped>
.timeline-item {
	display: flex;
	gap: 1rem;
	align-items: stretch;
}

.timeline-marker {
	display: flex;
	flex-direction: column;
	align-items: center;
	flex-shrink: 0;
	width: 20px;
	padding-top: 4px;
}

.timeline-dot {
	width: 14px;
	height: 14px;
	border-radius: 50%;
	background: var(--color-primary);
	border: 3px solid var(--color-main-background);
	box-shadow: 0 0 0 1px var(--color-border);
	z-index: 1;
}

.timeline-dot--birth {
	background: #2ea043;
}

.timeline-dot--death {
	background: #8c8c8c;
}

.timeline-dot--marriage {
	background: #e85d75;
}

.timeline-dot--education {
	background: #0077cc;
}

.timeline-dot--military {
	background: #7c5c2e;
}

.timeline-line {
	flex: 1;
	width: 2px;
	background: var(--color-border);
	margin-top: 6px;
	min-height: 24px;
}

.timeline-content {
	flex: 1;
	min-width: 0;
	background: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	padding: .85rem 1rem;
	margin-bottom: .75rem;
	transition: border-color .2s ease, box-shadow .2s ease;
}

.timeline-content:hover {
	border-color: var(--color-primary-element);
	box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
}

.timeline-content__top {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: .5rem;
}

.timeline-header {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: .5rem;
	min-width: 0;
	margin-bottom: .35rem;
}

.timeline-actions {
	flex-shrink: 0;
	margin: -.25rem -.35rem 0 0;
}

.timeline-badge {
	display: inline-block;
	padding: 2px 8px;
	border-radius: var(--border-radius-pill);
	font-size: .72rem;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: .03em;
	background: var(--color-background-dark);
	color: var(--color-text-maxcontrast);
}

.timeline-badge--birth {
	background: rgba(46, 160, 67, .15);
	color: #2ea043;
}

.timeline-badge--death {
	background: rgba(140, 140, 140, .15);
	color: var(--color-text-maxcontrast);
}

.timeline-badge--marriage {
	background: rgba(232, 93, 117, .15);
	color: #e85d75;
}

.timeline-badge--education {
	background: rgba(0, 119, 204, .12);
	color: #0077cc;
}

.timeline-badge--military {
	background: rgba(124, 92, 46, .15);
	color: #7c5c2e;
}

.timeline-date {
	font-size: .8rem;
	color: var(--color-text-maxcontrast);
	font-weight: 500;
}

.timeline-title {
	margin: 0 0 .25rem;
	font-size: .95rem;
	font-weight: 600;
	color: var(--color-main-text);
}

.timeline-meta {
	display: flex;
	align-items: flex-start;
	gap: .3rem;
	margin-top: .2rem;
	font-size: .85rem;
	line-height: 1.4;
	color: var(--color-text-maxcontrast);
}

.timeline-meta__icon {
	flex-shrink: 0;
	margin-top: .1rem;
	opacity: .85;
}

.timeline-description {
	margin: .45rem 0 0;
	font-size: .875rem;
	line-height: 1.5;
	color: var(--color-text-maxcontrast);
}

.timeline-item--last .timeline-content {
	margin-bottom: 0;
}
</style>
