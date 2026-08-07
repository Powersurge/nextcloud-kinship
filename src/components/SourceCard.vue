<script setup lang="ts">
import { computed } from 'vue'
import { NcActions, NcActionButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import LinkVariant from 'vue-material-design-icons/LinkVariant.vue'
import BookOpenPageVariantOutline from 'vue-material-design-icons/BookOpenPageVariantOutline.vue'
import { useSourceStore } from '@/stores/use-source-store.ts'
import type { Source } from '@/types'

const props = withDefaults(defineProps<{
	source: Record<string, any>
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

const { getTypeLabel, getMetaLine } = useSourceStore()

const typeLabel = computed(() => getTypeLabel(props.source as Source))
const metaLine = computed(() => getMetaLine(props.source as Source))
</script>

<template>
	<div
		class="source-card"
		role="button"
		tabindex="0"
		@click="emit('click')"
		@keydown.enter.prevent="emit('click')">
		<div class="source-card__body">
			<div class="source-card__header">
				<span v-if="typeLabel" class="source-card__badge">{{ typeLabel }}</span>
			</div>

			<h3 class="source-card__name">
				{{ source.title }}
			</h3>

			<p v-if="metaLine" class="source-card__meta">
				<BookOpenPageVariantOutline :size="14" class="source-card__meta-icon" />
				{{ metaLine }}
			</p>

			<p v-if="source.url" class="source-card__meta">
				<LinkVariant :size="14" class="source-card__meta-icon" />
			<a
				:href="source.url"
				target="_blank"
				rel="noopener noreferrer"
				@click.stop>
				{{ source.url }}
			</a>
			</p>
		</div>

		<NcActions
			v-if="canEdit || canDelete"
			class="source-card__actions"
			@click.stop>
			<NcActionButton v-if="canEdit" close-after-click @click="emit('edit')">
				<template #icon>
					<Pencil :size="20" />
				</template>
				Edit
			</NcActionButton>
			<NcActionButton v-if="canDelete" close-after-click @click="emit('delete')">
				<template #icon>
					<Delete :size="20" />
				</template>
				Delete
			</NcActionButton>
		</NcActions>
	</div>
</template>

<style scoped>
.source-card {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 8px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large, 8px);
	padding: 12px 16px;
	background-color: var(--color-main-background);
	cursor: pointer;
	transition: border-color .15s, box-shadow .15s;
}

.source-card:hover,
.source-card:focus-visible {
	border-color: var(--color-primary-element);
	box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
	outline: none;
}

.source-card__header {
	margin-bottom: 4px;
}

.source-card__badge {
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

.source-card__name {
	margin: 0 0 6px;
	font-size: 1rem;
}

.source-card__meta {
	display: flex;
	align-items: flex-start;
	gap: 0.3rem;
	margin: 0 0 2px;
	color: var(--color-text-maxcontrast);
	font-size: 0.9em;
	line-height: 1.4;
	word-break: break-word;
}

.source-card__meta-icon {
	flex-shrink: 0;
	margin-top: 0.1rem;
	opacity: 0.85;
}

.source-card__meta a {
	color: var(--color-main-text);
}
</style>
