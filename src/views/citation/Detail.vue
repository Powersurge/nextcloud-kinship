<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { NcButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import BookOpenPageVariantOutline from 'vue-material-design-icons/BookOpenPageVariantOutline.vue'
import CalendarOutline from 'vue-material-design-icons/CalendarOutline.vue'
import EntityDetailLayout from '@/components/detail/EntityDetailLayout.vue'
import CitationModal from '@/components/modals/CitationModal.vue'
import MediaSection from '@/components/media/MediaSection.vue'
import { fetchCitation } from '@/api/citations.ts'
import type { Citation } from '@/types'
import { CITATION_CONFIDENCE_OPTIONS } from '@/constants.ts'
import { subjectLabel, subjectRoute } from '@/utils/subjects.ts'
import { useCitationStore } from '@/stores/use-citation-store.ts'
import { usePermissionsStore } from '@/stores/use-permissions-store.ts'

const props = defineProps<{
	id: string | number
}>()

const { canEdit } = storeToRefs(usePermissionsStore())
const { upsertCitation } = useCitationStore()

const activeTab = ref('details')
const loading = ref(false)
const error = ref<string | null>(null)
const citation = ref<Citation | null>(null)
const modalOpen = ref(false)

const citationId = computed(() => {
	const value = Number(props.id)
	return Number.isFinite(value) && value > 0 ? value : null
})

const title = computed(() => citation.value?.source?.title || 'Citation')
const subtitle = computed(() => confidenceLabel(citation.value?.confidence ?? null) ?? '')

const backTo = computed(() =>
	citation.value ? `/sources/${citation.value.sourceId}` : '/sources',
)
const backLabel = computed(() =>
	citation.value ? 'Back to source' : 'Back to sources',
)

const subjectTo = computed(() =>
	citation.value
		? subjectRoute(citation.value.subjectType, citation.value.subjectId)
		: null,
)

const tabs = [
	{ id: 'details', label: 'Details' },
	{ id: 'media', label: 'Media' },
]

function confidenceLabel(value: string | null): string | null {
	if (!value) {
		return null
	}
	return CITATION_CONFIDENCE_OPTIONS.find((option) => option.value === value)?.label ?? value
}

async function loadCitation(id: number) {
	loading.value = true
	error.value = null
	try {
		citation.value = await fetchCitation(id)
	} catch (e) {
		console.error('Failed to load citation', e)
		citation.value = null
		error.value = 'Could not load this citation.'
	} finally {
		loading.value = false
	}
}

watch(
	citationId,
	(id) => {
		activeTab.value = 'details'
		if (id) {
			loadCitation(id)
		} else {
			citation.value = null
			error.value = 'Invalid citation id.'
		}
	},
	{ immediate: true },
)

function openEdit() {
	if (citation.value) {
		modalOpen.value = true
	}
}

async function onSaved(saved: Record<string, unknown>) {
	upsertCitation(saved as Citation)
	if (citationId.value) {
		// Refetch so the hydrated source summary stays accurate.
		await loadCitation(citationId.value)
	}
}
</script>

<template>
	<EntityDetailLayout
		:title="title"
		:subtitle="subtitle"
		:back-to="backTo"
		:back-label="backLabel"
		:tabs="tabs"
		:active-tab="activeTab"
		:loading="loading && !citation"
		:error="error"
		@update:active-tab="activeTab = $event">
		<template #actions>
			<NcButton
				v-if="citation && canEdit"
				variant="primary"
				@click="openEdit">
				<template #icon>
					<Pencil :size="20" />
				</template>
				Edit
			</NcButton>
		</template>

		<div v-if="activeTab === 'details' && citation" class="citation-overview">
			<div class="citation-overview__row">
				<span class="citation-overview__label">Source</span>
				<span class="citation-overview__value citation-overview__meta">
					<BookOpenPageVariantOutline :size="16" />
					<RouterLink :to="{ name: 'source-detail', params: { id: String(citation.sourceId) } }">
						{{ citation.source?.title ?? `Source #${citation.sourceId}` }}
					</RouterLink>
				</span>
			</div>
			<div class="citation-overview__row">
				<span class="citation-overview__label">Cited on</span>
				<span class="citation-overview__value">
					<RouterLink v-if="subjectTo" :to="subjectTo">
						{{ subjectLabel(citation.subjectType) }} #{{ citation.subjectId }}
					</RouterLink>
					<template v-else>
						{{ subjectLabel(citation.subjectType) }} #{{ citation.subjectId }}
					</template>
				</span>
			</div>
			<div class="citation-overview__row">
				<span class="citation-overview__label">Detail</span>
				<span v-if="citation.detail" class="citation-overview__value">{{ citation.detail }}</span>
				<span v-else class="citation-overview__empty">No detail recorded</span>
			</div>
			<div class="citation-overview__row">
				<span class="citation-overview__label">Transcription</span>
				<span
					v-if="citation.transcription"
					class="citation-overview__value citation-overview__value--pre">
					{{ citation.transcription }}
				</span>
				<span v-else class="citation-overview__empty">No transcription yet</span>
			</div>
			<div class="citation-overview__row">
				<span class="citation-overview__label">Confidence</span>
				<span v-if="subtitle" class="citation-overview__value">{{ subtitle }}</span>
				<span v-else class="citation-overview__empty">No confidence recorded</span>
			</div>
			<div class="citation-overview__row">
				<span class="citation-overview__label">Accessed</span>
				<span v-if="citation.accessedDate" class="citation-overview__value citation-overview__meta">
					<CalendarOutline :size="16" />
					{{ citation.accessedDate }}
				</span>
				<span v-else class="citation-overview__empty">No access date recorded</span>
			</div>
		</div>

		<MediaSection
			v-else-if="activeTab === 'media' && citationId"
			subject-type="citation"
			:subject-id="citationId"
			:show-primary-action="false" />
	</EntityDetailLayout>

	<CitationModal
		v-model:open="modalOpen"
		:citation="citation"
		@saved="onSaved" />
</template>

<style scoped>
.citation-overview {
	display: flex;
	flex-direction: column;
	gap: 1.1rem;
	max-width: 42rem;
}

.citation-overview__row {
	display: flex;
	flex-direction: column;
	gap: 0.25rem;
}

.citation-overview__label {
	font-weight: 700;
}

.citation-overview__value {
	color: var(--color-main-text);
	line-height: 1.5;
	word-break: break-word;
}

.citation-overview__value a {
	color: var(--color-main-text);
}

.citation-overview__value a:hover,
.citation-overview__meta a:hover {
	text-decoration: underline;
}

.citation-overview__value--pre {
	white-space: pre-wrap;
}

.citation-overview__meta {
	display: inline-flex;
	align-items: flex-start;
	gap: 0.35rem;
}

.citation-overview__meta a {
	color: var(--color-main-text);
}

.citation-overview__empty {
	color: var(--color-text-maxcontrast);
	font-style: italic;
	font-size: 0.95rem;
}
</style>
