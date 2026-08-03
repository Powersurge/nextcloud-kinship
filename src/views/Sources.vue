<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { NcButton, NcEmptyContent, NcLoadingIcon } from '@nextcloud/vue'
import BookOpenPageVariantOutline from 'vue-material-design-icons/BookOpenPageVariantOutline.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import SourceCard from '../components/SourceCard.vue'
import SourceModal from '../components/modals/SourceModal.vue'
import { deleteSource as deleteSourceApi, fetchSources } from '@/api/sources.ts'
import type { Source } from '@/types'
import { showConfirmation, showError } from '@nextcloud/dialogs'

const sources = ref<Source[]>([])
const loading = ref(true)

const modalOpen = ref(false)
const editing = ref<Source | null>(null)

async function loadSources() {
	loading.value = true
	try {
		sources.value = await fetchSources()
	} catch (e) {
		showError('Failed to load sources')
	} finally {
		loading.value = false
	}
}

function openCreate() {
	editing.value = null
	modalOpen.value = true
}

function openEdit(source: Source) {
	editing.value = source
	modalOpen.value = true
}

function onSaved() {
	loadSources()
}

async function deleteSource(source: Source) {
	const confirmed = await showConfirmation({
		name: 'Delete source?',
		text: `Delete “${source.title}”? All citations using this source will also be removed.`,
		labelReject: 'Cancel',
		severity: 'error',
	})
	if (!confirmed) {
		return
	}
	try {
		await deleteSourceApi(source.id)
		sources.value = sources.value.filter((item) => item.id !== source.id)
	} catch (e) {
		showError('Failed to delete source')
	}
}

onMounted(loadSources)
</script>

<template>
	<div class="sources">
		<div class="sources__header">
			<NcButton variant="primary" @click="openCreate">
				<template #icon>
					<Plus :size="20" />
				</template>
				New source
			</NcButton>
		</div>

		<NcLoadingIcon v-if="loading" :size="32" class="sources__loading" />

		<NcEmptyContent
			v-else-if="sources.length === 0"
			name="No sources yet"
			description="Add a source when you find a census, certificate, book, or website.">
			<template #icon>
				<BookOpenPageVariantOutline />
			</template>
			<template #action>
				<NcButton variant="primary" @click="openCreate">
					New source
				</NcButton>
			</template>
		</NcEmptyContent>

		<div v-else class="sources__list">
			<SourceCard
				v-for="source in sources"
				:key="source.id"
				:source="source"
				@edit="openEdit(source)"
				@delete="deleteSource(source)" />
		</div>

		<SourceModal
			v-model:open="modalOpen"
			:source="editing"
			@saved="onSaved" />
	</div>
</template>

<style scoped>
.sources {
	display: flex;
	flex-direction: column;
}

.sources__header {
	display: flex;
	gap: var(--default-grid-baseline);
	align-items: center;
	flex: 0 0;
	max-width: 100%;
	margin-block: var(--app-navigation-padding, 4px);
	margin-inline: calc(var(--default-clickable-area) + 2 * var(--app-navigation-padding, 4px)) var(--app-navigation-padding, 4px);
}

.sources__loading {
	margin-top: 32px;
}

.sources__list {
	display: flex;
	flex-direction: column;
	gap: 8px;
	margin: 16px;
}
</style>
