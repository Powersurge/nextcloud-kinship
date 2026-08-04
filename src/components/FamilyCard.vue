<script setup lang="ts">
import { computed } from 'vue'
import { NcActions, NcActionButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'

const props = defineProps<{
	family: Record<string, any>
}>()

const emit = defineEmits<{
	edit: []
	click: []
}>()

const typeLabels: Record<string, string> = {
	partnership: 'Partnership',
	single_parent: 'Single parent',
}

const name = computed(() => props.family.name || 'Unnamed family')
const typeLabel = computed(() =>
	props.family.type ? (typeLabels[props.family.type] ?? props.family.type) : '',
)
</script>

<template>
	<div
		class="family-card"
		role="button"
		tabindex="0"
		@click="emit('click')"
		@keydown.enter.prevent="emit('click')">
		<div class="family-card__body">
			<h3 class="family-card__name">
				{{ name }}
				<span v-if="family.isDerived" class="family-card__badge">Auto</span>
			</h3>

			<p v-if="typeLabel" class="family-card__meta">
				{{ typeLabel }}
			</p>

			<p v-if="family.description" class="family-card__meta">
				{{ family.description }}
			</p>
		</div>

		<NcActions class="family-card__actions" @click.stop>
			<NcActionButton @click="emit('edit')">
				<template #icon>
					<Pencil :size="20" />
				</template>
				Edit
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
	cursor: pointer;
}

.family-card:hover {
	border-color: var(--color-primary-element);
}

.family-card__name {
	margin: 0 0 4px;
	display: flex;
	align-items: center;
	gap: 8px;
}

.family-card__badge {
	font-size: 0.7em;
	font-weight: 600;
	text-transform: uppercase;
	padding: 2px 8px;
	border-radius: 999px;
	background: var(--color-background-hover);
	color: var(--color-text-maxcontrast);
}

.family-card__meta {
	margin: 0;
	color: var(--color-text-maxcontrast);
	font-size: 0.9em;
}
</style>
