<script setup lang="ts">
import { computed } from 'vue'
import { NcActions, NcActionButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import ArrowRight from 'vue-material-design-icons/ArrowRight.vue'
import { relationshipTypeLabel } from '../utils/relationships'
import { dateDisplay } from '../utils/date'

const props = defineProps<{
	relationship: Record<string, any>
	aName: string
	bName: string
}>()

const emit = defineEmits<{
	edit: []
	delete: []
}>()

const typeLabel = computed(() => relationshipTypeLabel(props.relationship.type))
const when = computed(() => dateDisplay(props.relationship.date))
</script>

<template>
	<div class="rel-card">
		<div class="rel-card__body">
			<div class="rel-card__line">
				<strong>{{ aName }}</strong>
				<span class="rel-card__type">{{ typeLabel }} of</span>
				<ArrowRight :size="16" />
				<strong>{{ bName }}</strong>
			</div>

			<p v-if="when" class="rel-card__meta">
				{{ when }}
			</p>
			<p v-if="relationship.note" class="rel-card__meta">
				{{ relationship.note }}
			</p>
		</div>

		<NcActions class="rel-card__actions">
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
.rel-card {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 8px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large, 8px);
	padding: 12px 16px;
	background-color: var(--color-main-background);
}

.rel-card__line {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-wrap: wrap;
}

.rel-card__type {
	color: var(--color-text-maxcontrast);
	font-size: 0.9em;
}

.rel-card__meta {
	margin: 4px 0 0;
	color: var(--color-text-maxcontrast);
	font-size: 0.9em;
}
</style>
