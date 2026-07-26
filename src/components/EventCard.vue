<script setup lang="ts">
import { computed } from 'vue'
import { NcActions, NcActionButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import { dateDisplay } from '@/utils/date.ts'

const props = defineProps<{
	event: Record<string, any>
}>()

const emit = defineEmits<{
	edit: []
	delete: []
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
</script>

<template>
	<div class="event-card">
		<div class="event-card__body">
			<h3 class="event-card__name">
				{{ heading }}
			</h3>

			<p class="event-card__meta">
				{{ typeLabel }}
			</p>

			<p v-if="when" class="event-card__meta">
				{{ when }}
			</p>
		</div>

		<NcActions class="event-card__actions">
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
}

.event-card__name {
	margin: 0 0 4px;
}

.event-card__meta {
	margin: 0;
	color: var(--color-text-maxcontrast);
	font-size: 0.9em;
}
</style>
