<template>
	<div class="timeline-item">
		<div class="timeline-marker">
			<div class="timeline-dot" />
			<div class="timeline-line" />
		</div>

		<div class="timeline-content">
			<div class="timeline-date">
				{{ when }}
			</div>

			<h3 class="timeline-title capitalize">
				{{ heading }}
			</h3>

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
import { dateDisplay } from '@/utils/date.ts'

interface Event {
	title?: string
	type: string
	date: unknown
	description?: string
}

const props = defineProps<{
	event: Event
}>()

const when = computed(() => dateDisplay(props.event.date))
const heading = computed(() => props.event.title || props.event.type)
</script>

<style scoped>
.timeline-item {
	display: flex;
	gap: 1rem;
	align-items: flex-start;
}

.timeline-marker {
	display: flex;
	flex-direction: column;
	align-items: center;
	flex-shrink: 0;
	width: 20px;
}

.timeline-dot {
	width: 12px;
	height: 12px;
	border-radius: 50%;
	background: var(--color-primary);
	border: 3px solid var(--color-main-background);
	box-shadow: 0 0 0 1px var(--color-border);
	z-index: 1;
}

.timeline-line {
	flex: 1;
	width: 2px;
	background: var(--color-border);
	margin-top: 4px;
	min-height: 32px;
}

.timeline-content {
	flex: 1;
	background: var(--color-background-dark);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	padding: .85rem 1rem;
	transition: background-color .2s ease;
}

.timeline-content:hover {
	background: var(--color-background-hover);
}

.timeline-date {
	font-size: .8rem;
	color: var(--color-text-maxcontrast);
	margin-bottom: .35rem;
	font-weight: 500;
}

.timeline-title {
	margin: 0;
	font-size: 1rem;
	font-weight: 600;
	color: var(--color-main-text);
}

.timeline-description {
	margin: .5rem 0 0;
	font-size: .9rem;
	line-height: 1.5;
	color: var(--color-text-maxcontrast);
}
</style>
