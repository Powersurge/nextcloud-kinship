<script setup lang="ts">
import { computed, watch } from 'vue'
import { NcActions, NcActionButton, NcButton } from '@nextcloud/vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import BookOpenPageVariantOutline from 'vue-material-design-icons/BookOpenPageVariantOutline.vue'
import { storeToRefs } from 'pinia'
import { useCitationStore } from '@/stores/use-citation-store.ts'
import { deleteCitation as deleteCitationApi } from '@/api/citations.ts'
import type { Citation } from '@/types'
import { CITATION_CONFIDENCE_OPTIONS } from '@/constants.ts'
import { showConfirmation, showError } from '@nextcloud/dialogs'

const props = withDefaults(defineProps<{
	subjectType: string
	subjectId: number
	compact?: boolean
}>(), {
	compact: false,
})

const citationStore = useCitationStore()
const { citationsBySubject } = storeToRefs(citationStore)
const { openCreateCitationForm, openEditCitationForm, removeCitation, loadForSubject } = citationStore

const citations = computed(() =>
	citationsBySubject.value[`${props.subjectType}:${props.subjectId}`] ?? [],
)

watch(
	() => [props.subjectType, props.subjectId] as const,
	([subjectType, subjectId]) => {
		if (subjectType && subjectId) {
			loadForSubject(subjectType, subjectId)
		}
	},
	{ immediate: true },
)

function confidenceLabel(value: string | null): string | null {
	if (!value) {
		return null
	}
	return CITATION_CONFIDENCE_OPTIONS.find((option) => option.value === value)?.label ?? value
}

function openCreate() {
	openCreateCitationForm(props.subjectType, props.subjectId)
}

async function onDelete(citation: Citation) {
	const confirmed = await showConfirmation({
		name: 'Delete citation?',
		text: 'This removes the citation link. The source itself is kept.',
		labelReject: 'Cancel',
		severity: 'error',
	})
	if (!confirmed) {
		return
	}
	try {
		await deleteCitationApi(citation.id)
		removeCitation(citation)
	} catch (e) {
		showError('Failed to delete citation')
	}
}
</script>

<template>
	<div class="citation-list" :class="{ 'citation-list--compact': compact }">
		<div class="citation-list__header">
			<span class="citation-list__label">
				<BookOpenPageVariantOutline :size="16" />
				Sources
				<span v-if="citations.length" class="citation-list__count">
					{{ citations.length }}
				</span>
			</span>
			<NcButton size="small" @click="openCreate">
				<template #icon>
					<Plus :size="16" />
				</template>
				Cite
			</NcButton>
		</div>

		<ul v-if="citations.length" class="citation-list__items">
			<li
				v-for="citation in citations"
				:key="citation.id"
				class="citation-list__item">
				<div class="citation-list__main">
					<div class="citation-list__title">
						{{ citation.source?.title ?? `Source #${citation.sourceId}` }}
					</div>
					<div v-if="citation.detail" class="citation-list__detail">
						{{ citation.detail }}
					</div>
					<div
						v-if="confidenceLabel(citation.confidence)"
						class="citation-list__confidence">
						{{ confidenceLabel(citation.confidence) }}
					</div>
				</div>
				<NcActions>
					<NcActionButton close-after-click @click="openEditCitationForm(citation)">
						<template #icon>
							<Pencil :size="20" />
						</template>
						Edit
					</NcActionButton>
					<NcActionButton close-after-click @click="onDelete(citation)">
						<template #icon>
							<Delete :size="20" />
						</template>
						Delete
					</NcActionButton>
				</NcActions>
			</li>
		</ul>
		<p v-else class="citation-list__empty">
			No citations yet
		</p>
	</div>
</template>

<style scoped>
.citation-list {
	margin-top: 0.5rem;
}

.citation-list__header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 0.5rem;
	margin-bottom: 0.35rem;
}

.citation-list__label {
	display: inline-flex;
	align-items: center;
	gap: 0.35rem;
	font-size: 0.8rem;
	font-weight: 600;
	color: var(--color-text-maxcontrast);
}

.citation-list__count {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 1.25rem;
	height: 1.25rem;
	padding: 0 0.3rem;
	border-radius: var(--border-radius-pill);
	background: var(--color-background-dark);
	font-size: 0.7rem;
}

.citation-list__items {
	list-style: none;
	margin: 0;
	padding: 0;
	display: flex;
	flex-direction: column;
	gap: 0.35rem;
}

.citation-list__item {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 0.5rem;
	padding: 0.4rem 0.55rem;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
	background: var(--color-background-dark);
}

.citation-list__title {
	font-size: 0.85rem;
	font-weight: 500;
}

.citation-list__detail,
.citation-list__confidence {
	font-size: 0.78rem;
	color: var(--color-text-maxcontrast);
	margin-top: 0.1rem;
}

.citation-list__empty {
	margin: 0;
	font-size: 0.8rem;
	color: var(--color-text-maxcontrast);
}

.citation-list--compact .citation-list__item {
	padding: 0.3rem 0.45rem;
}
</style>
