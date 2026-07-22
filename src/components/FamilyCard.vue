<script setup lang="ts">
import { NcActions, NcActionButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'

defineProps<{
	family: Record<string, any>
}>()

const emit = defineEmits<{
	edit: []
	delete: []
}>()
</script>

<template>
	<div class="family-card">
		<div class="family-card__body">
			<h3 class="family-card__name">
				{{ family.name }}
			</h3>

			<p v-if="family.description" class="family-card__meta">
				{{ family.description }}
			</p>
		</div>

		<NcActions class="family-card__actions">
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
.family-card {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 8px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large, 8px);
	padding: 12px 16px;
	background-color: var(--color-main-background);
}

.family-card__name {
	margin: 0 0 4px;
}

.family-card__meta {
	margin: 0;
	color: var(--color-text-maxcontrast);
	font-size: 0.9em;
}
</style>
