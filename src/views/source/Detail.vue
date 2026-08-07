<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { NcButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import LinkVariant from 'vue-material-design-icons/LinkVariant.vue'
import EntityDetailLayout from '@/components/detail/EntityDetailLayout.vue'
import SourceModal from '@/components/modals/SourceModal.vue'
import MediaSection from '@/components/media/MediaSection.vue'
import { fetchSource, fetchSourceCitations } from '@/api/sources.ts'
import type { Citation, Source } from '@/types'
import { CITATION_CONFIDENCE_OPTIONS } from '@/constants.ts'
import { subjectLabel, subjectRoute } from '@/utils/subjects.ts'
import { useSourceStore } from '@/stores/use-source-store.ts'
import { usePermissionsStore } from '@/stores/use-permissions-store.ts'

const props = defineProps<{
	id: string | number
}>()

const { canEdit } = storeToRefs(usePermissionsStore())
const { getTypeLabel } = useSourceStore()

const activeTab = ref('details')
const loading = ref(false)
const error = ref<string | null>(null)
const source = ref<Source | null>(null)
const citations = ref<Citation[]>([])
const citationsLoading = ref(false)
const modalOpen = ref(false)

const sourceId = computed(() => {
	const value = Number(props.id)
	return Number.isFinite(value) && value > 0 ? value : null
})

const title = computed(() => source.value?.title || 'Source')
const subtitle = computed(() => (source.value ? (getTypeLabel(source.value) ?? '') : ''))

const tabs = [
	{ id: 'details', label: 'Details' },
	{ id: 'citations', label: 'Citations' },
	{ id: 'media', label: 'Media' },
]

function confidenceLabel(value: string | null): string | null {
	if (!value) {
		return null
	}
	return CITATION_CONFIDENCE_OPTIONS.find((option) => option.value === value)?.label ?? value
}

async function loadSource(id: number) {
	loading.value = true
	error.value = null
	try {
		source.value = await fetchSource(id)
	} catch (e) {
		console.error('Failed to load source', e)
		source.value = null
		error.value = 'Could not load this source.'
	} finally {
		loading.value = false
	}
}

async function loadCitations(id: number) {
	citationsLoading.value = true
	try {
		citations.value = await fetchSourceCitations(id)
	} catch (e) {
		console.error('Failed to load source citations', e)
		citations.value = []
	} finally {
		citationsLoading.value = false
	}
}

watch(
	sourceId,
	(id) => {
		activeTab.value = 'details'
		if (id) {
			loadSource(id)
			loadCitations(id)
		} else {
			source.value = null
			citations.value = []
			error.value = 'Invalid source id.'
		}
	},
	{ immediate: true },
)

function openEdit() {
	if (source.value) {
		modalOpen.value = true
	}
}

function onSaved(saved: Source) {
	source.value = saved
}
</script>

<template>
	<EntityDetailLayout
		:title="title"
		:subtitle="subtitle"
		back-to="/sources"
		back-label="Back to sources"
		:tabs="tabs"
		:active-tab="activeTab"
		:loading="loading && !source"
		:error="error"
		@update:active-tab="activeTab = $event">
		<template #actions>
			<NcButton
				v-if="source && canEdit"
				variant="primary"
				@click="openEdit">
				<template #icon>
					<Pencil :size="20" />
				</template>
				Edit
			</NcButton>
		</template>

		<div v-if="activeTab === 'details' && source" class="source-overview">
			<div class="source-overview__row">
				<span class="source-overview__label">Title</span>
				<span class="source-overview__value">{{ source.title }}</span>
			</div>
			<div class="source-overview__row">
				<span class="source-overview__label">Type</span>
				<span v-if="subtitle" class="source-overview__value">{{ subtitle }}</span>
				<span v-else class="source-overview__empty">No type recorded</span>
			</div>
			<div class="source-overview__row">
				<span class="source-overview__label">Author</span>
				<span v-if="source.author" class="source-overview__value">{{ source.author }}</span>
				<span v-else class="source-overview__empty">No author recorded</span>
			</div>
			<div class="source-overview__row">
				<span class="source-overview__label">Publication</span>
				<span v-if="source.publication" class="source-overview__value">{{ source.publication }}</span>
				<span v-else class="source-overview__empty">No publication recorded</span>
			</div>
			<div class="source-overview__row">
				<span class="source-overview__label">Repository</span>
				<span v-if="source.repository" class="source-overview__value">{{ source.repository }}</span>
				<span v-else class="source-overview__empty">No repository recorded</span>
			</div>
			<div class="source-overview__row">
				<span class="source-overview__label">URL</span>
				<span v-if="source.url" class="source-overview__value source-overview__meta">
					<LinkVariant :size="16" />
					<a :href="source.url" target="_blank" rel="noopener noreferrer">
						{{ source.url }}
					</a>
				</span>
				<span v-else class="source-overview__empty">No URL recorded</span>
			</div>
			<div class="source-overview__row">
				<span class="source-overview__label">Notes</span>
				<span
					v-if="source.notes"
					class="source-overview__value source-overview__value--pre">
					{{ source.notes }}
				</span>
				<span v-else class="source-overview__empty">No notes yet</span>
			</div>
		</div>

		<div v-else-if="activeTab === 'citations'" class="source-citations">
			<p v-if="citationsLoading" class="source-citations__status">
				Loading citations…
			</p>
			<p v-else-if="citations.length === 0" class="source-citations__status">
				This source has not been cited yet.
			</p>
			<ul v-else class="source-citations__items">
				<li
					v-for="citation in citations"
					:key="citation.id"
					class="source-citations__item">
					<div class="source-citations__main">
						<RouterLink
							class="source-citations__title"
							:to="{ name: 'citation-detail', params: { id: String(citation.id) } }">
							{{ citation.detail || `Citation #${citation.id}` }}
						</RouterLink>
						<div class="source-citations__subject">
							Cited on:
							<RouterLink
								v-if="subjectRoute(citation.subjectType, citation.subjectId)"
								:to="subjectRoute(citation.subjectType, citation.subjectId)!">
								{{ subjectLabel(citation.subjectType) }} #{{ citation.subjectId }}
							</RouterLink>
							<span v-else>
								{{ subjectLabel(citation.subjectType) }} #{{ citation.subjectId }}
							</span>
						</div>
						<div
							v-if="confidenceLabel(citation.confidence)"
							class="source-citations__confidence">
							{{ confidenceLabel(citation.confidence) }}
						</div>
					</div>
				</li>
			</ul>
		</div>

		<MediaSection
			v-else-if="activeTab === 'media' && sourceId"
			subject-type="source"
			:subject-id="sourceId" />
	</EntityDetailLayout>

	<SourceModal
		v-model:open="modalOpen"
		:source="source"
		@saved="onSaved" />
</template>

<style scoped>
.source-overview {
	display: flex;
	flex-direction: column;
	gap: 1.1rem;
	max-width: 42rem;
}

.source-overview__row {
	display: flex;
	flex-direction: column;
	gap: 0.25rem;
}

.source-overview__label {
	font-weight: 700;
}

.source-overview__value {
	color: var(--color-main-text);
	line-height: 1.5;
	word-break: break-word;
}

.source-overview__value--pre {
	white-space: pre-wrap;
}

.source-overview__meta {
	display: inline-flex;
	align-items: flex-start;
	gap: 0.35rem;
}

.source-overview__meta a {
	color: var(--color-main-text);
}

.source-overview__empty {
	color: var(--color-text-maxcontrast);
	font-style: italic;
	font-size: 0.95rem;
}

.source-citations__status {
	margin: 0;
	color: var(--color-text-maxcontrast);
}

.source-citations__items {
	list-style: none;
	margin: 0;
	padding: 0;
	display: flex;
	flex-direction: column;
	gap: 0.5rem;
	max-width: 42rem;
}

.source-citations__item {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 0.5rem;
	padding: 0.6rem 0.75rem;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
	background: var(--color-background-dark);
}

.source-citations__title {
	font-weight: 600;
	color: var(--color-main-text);
}

.source-citations__title:hover {
	text-decoration: underline;
}

.source-citations__subject {
	margin-top: 0.15rem;
	font-size: 0.85rem;
	color: var(--color-text-maxcontrast);
}

.source-citations__subject a {
	color: var(--color-main-text);
}

.source-citations__subject a:hover {
	text-decoration: underline;
}

.source-citations__confidence {
	margin-top: 0.15rem;
	font-size: 0.8rem;
	color: var(--color-text-maxcontrast);
}
</style>
