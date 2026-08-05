<script setup lang="ts">
import { computed } from 'vue'
import { NcActions, NcActionButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import MapMarkerOutline from 'vue-material-design-icons/MapMarkerOutline.vue'
import CalendarOutline from 'vue-material-design-icons/CalendarOutline.vue'
import { dateDisplay } from '@/utils/date.ts'
import { placeDisplay } from '@/utils/place.ts'

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
</script>

<template>
	<div
		class="event-card"
		role="button"
		tabindex="0"
		@click="emit('click')"
		@keydown.enter.prevent="emit('click')">
		<div class="event-card__body">
			<div class="event-card__header">
				<span class="event-card__badge">{{ typeLabel }}</span>
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
</template>

<style scoped>
.event-card {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 8px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large, 8px);
	padding: 12px 16px;
	background-color: var(--color-main-background);
	cursor: pointer;
}

.event-card:hover {
	border-color: var(--color-primary-element);
}

.event-card__header {
	margin-bottom: 4px;
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

.event-card__name {
	margin: 0 0 6px;
	font-size: 1rem;
}

.event-card__meta {
	display: flex;
	align-items: flex-start;
	gap: 0.3rem;
	margin: 0 0 2px;
	color: var(--color-text-maxcontrast);
	font-size: 0.9em;
	line-height: 1.4;
}

.event-card__meta-icon {
	flex-shrink: 0;
	margin-top: 0.1rem;
	opacity: 0.85;
}
</style>
