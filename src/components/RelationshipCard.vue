<script setup lang="ts">
import { computed } from 'vue'
import { NcActions, NcActionButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import ArrowRight from 'vue-material-design-icons/ArrowRight.vue'
import {
	isPartnershipType,
	relationshipTypeLabel,
} from '@/utils/relationships.ts'
import { dateDisplay } from '@/utils/date.ts'

const props = withDefaults(defineProps<{
	relationship: Record<string, any>
	aName: string
	bName: string
	canEdit?: boolean
	canDelete?: boolean
}>(), {
	canEdit: true,
	canDelete: true,
})

const emit = defineEmits<{
	edit: []
	delete: []
}>()

const typeLabel = computed(() => relationshipTypeLabel(props.relationship.type))
const when = computed(() => dateDisplay(props.relationship.date))
const isPartnership = computed(() => isPartnershipType(props.relationship.type))
</script>

<template>
	<div class="rel-card">
		<div class="rel-card__body">
			<div v-if="isPartnership" class="rel-card__line">
				<strong>{{ aName }}</strong>
				<span class="rel-card__type">and</span>
				<strong>{{ bName }}</strong>
				<span class="rel-card__status">{{ typeLabel }}</span>
			</div>
			<div v-else class="rel-card__line">
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

		<NcActions v-if="canEdit || canDelete" class="rel-card__actions">
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

.rel-card__status {
	font-size: 0.85em;
	font-weight: 600;
	padding: 2px 8px;
	border-radius: var(--border-radius-pill);
	background: var(--color-primary-element-light);
}

.rel-card__meta {
	margin: 4px 0 0;
	color: var(--color-text-maxcontrast);
	font-size: 0.9em;
}
</style>
