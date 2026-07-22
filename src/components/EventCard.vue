<script setup lang="ts">
import { NcActions, NcActionButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'

defineProps<{
	event: Record<string, any>
}>()

const emit = defineEmits<{
	edit: []
	delete: []
}>()
</script>

<template>
	<div class="event-card">
		<div class="event-card__body">
			<h3 class="event-card__name">
				{{ event.title }}
			</h3>

			<p class="event-card__meta">
				{{ event.type }}
			</p>

			<p v-if="event.eventDate" class="event-card__meta">
				{{ event.eventDate }}
			</p>

			<p v-if="event.location" class="event-card__meta">
				{{ event.location }}
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
