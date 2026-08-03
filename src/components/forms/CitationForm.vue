<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { NcButton, NcCheckboxRadioSwitch, NcSelect, NcTextArea, NcTextField } from '@nextcloud/vue'
import { CITATION_CONFIDENCE_OPTIONS, SOURCE_TYPE_OPTIONS } from '@/constants.ts'
import { createSource, fetchSources } from '@/api/sources.ts'
import { getFieldErrors, type FieldErrors } from '@/utils/api-errors.ts'
import type { SelectOption, Source } from '@/types'

interface CitationFormState {
	sourceId: number | null
	detail: string
	transcription: string
	confidence: string | null
	accessedDate: string
	createNewSource: boolean
	newSourceTitle: string
	newSourceType: string | null
	newSourceAuthor: string
}

const props = defineProps<{
	citation?: Record<string, any> | null
	saving?: boolean
	fieldErrors?: FieldErrors | null
}>()

const emit = defineEmits<{
	submit: [value: Record<string, unknown>]
	cancel: []
}>()

const sources = ref<Source[]>([])
const loadingSources = ref(false)
const creatingSource = ref(false)
const sourceFieldErrors = ref<FieldErrors | null>(null)
const form = reactive<CitationFormState>(emptyForm())

const sourceOptions = computed<SelectOption[]>(() =>
	sources.value.map((source) => ({
		value: source.id,
		label: source.author ? `${source.title} — ${source.author}` : source.title,
	})),
)

function emptyForm(): CitationFormState {
	return {
		sourceId: null,
		detail: '',
		transcription: '',
		confidence: null,
		accessedDate: '',
		createNewSource: false,
		newSourceTitle: '',
		newSourceType: null,
		newSourceAuthor: '',
	}
}

function fieldError(field: string): string {
	return props.fieldErrors?.[field]?.[0]
		?? sourceFieldErrors.value?.[field]?.[0]
		?? ''
}

async function loadSources() {
	loadingSources.value = true
	try {
		sources.value = await fetchSources()
	} catch (e) {
		console.error('Failed to load sources', e)
		sources.value = []
	} finally {
		loadingSources.value = false
	}
}

watch(
	() => props.citation,
	(citation) => {
		Object.assign(form, emptyForm())
		sourceFieldErrors.value = null
		if (citation) {
			form.sourceId = citation.sourceId ?? null
			form.detail = citation.detail ?? ''
			form.transcription = citation.transcription ?? ''
			form.confidence = citation.confidence ?? null
			form.accessedDate = citation.accessedDate ?? ''
			form.createNewSource = false
		}
	},
	{ immediate: true, deep: true },
)

watch(
	() => form.createNewSource,
	() => {
		sourceFieldErrors.value = null
	},
)

onMounted(loadSources)

async function resolveSourceId(): Promise<number | null> {
	if (!form.createNewSource) {
		return form.sourceId
	}

	creatingSource.value = true
	sourceFieldErrors.value = null
	try {
		const source = await createSource({
			title: form.newSourceTitle,
			sourceType: form.newSourceType || null,
			author: form.newSourceAuthor,
		})
		sources.value = [...sources.value, source].sort((a, b) => a.title.localeCompare(b.title))
		form.sourceId = source.id
		form.createNewSource = false
		return source.id
	} catch (e) {
		sourceFieldErrors.value = getFieldErrors(e)
		console.error('Failed to create source', e)
		return null
	} finally {
		creatingSource.value = false
	}
}

async function onSubmit() {
	const sourceId = await resolveSourceId()
	if (form.createNewSource && sourceId === null) {
		return
	}

	emit('submit', {
		sourceId,
		detail: form.detail,
		transcription: form.transcription,
		confidence: form.confidence || null,
		accessedDate: form.accessedDate,
	})
}
</script>

<template>
	<form class="citation-form" @submit.prevent="onSubmit">
		<NcCheckboxRadioSwitch
			v-model="form.createNewSource"
			type="switch"
			:disabled="saving || creatingSource || Boolean(citation?.id)">
			Create a new source
		</NcCheckboxRadioSwitch>

		<template v-if="form.createNewSource">
			<NcTextField
				v-model="form.newSourceTitle"
				label="Source title"
				:disabled="saving || creatingSource"
				:error="Boolean(fieldError('title'))"
				:helper-text="fieldError('title')" />
			<div class="citation-form__field">
				<NcSelect
					v-model="form.newSourceType"
					input-label="Source type"
					:options="SOURCE_TYPE_OPTIONS"
					:reduce="(option) => option.value"
					label="label"
					:clearable="true"
					:disabled="saving || creatingSource" />
				<p v-if="fieldError('sourceType')" class="citation-form__error">
					{{ fieldError('sourceType') }}
				</p>
			</div>
			<NcTextField
				v-model="form.newSourceAuthor"
				label="Author"
				:disabled="saving || creatingSource"
				:error="Boolean(fieldError('author'))"
				:helper-text="fieldError('author')" />
		</template>

		<div v-else class="citation-form__field">
			<NcSelect
				v-model="form.sourceId"
				input-label="Source"
				:options="sourceOptions"
				:reduce="(option) => option.value"
				label="label"
				:clearable="false"
				:loading="loadingSources"
				:disabled="saving || creatingSource" />
			<p v-if="fieldError('sourceId')" class="citation-form__error">
				{{ fieldError('sourceId') }}
			</p>
		</div>

		<NcTextField
			v-model="form.detail"
			label="Detail (page, entry, film…)"
			:disabled="saving || creatingSource"
			:error="Boolean(fieldError('detail'))"
			:helper-text="fieldError('detail')" />

		<div class="citation-form__field">
			<NcSelect
				v-model="form.confidence"
				input-label="Confidence"
				:options="CITATION_CONFIDENCE_OPTIONS"
				:reduce="(option) => option.value"
				label="label"
				:clearable="true"
				:disabled="saving || creatingSource" />
			<p v-if="fieldError('confidence')" class="citation-form__error">
				{{ fieldError('confidence') }}
			</p>
		</div>

		<NcTextField
			v-model="form.accessedDate"
			label="Accessed date (YYYY-MM-DD)"
			:disabled="saving || creatingSource"
			:error="Boolean(fieldError('accessedDate'))"
			:helper-text="fieldError('accessedDate')" />

		<NcTextArea
			v-model="form.transcription"
			label="Transcription / excerpt"
			:disabled="saving || creatingSource"
			:error="Boolean(fieldError('transcription'))"
			:helper-text="fieldError('transcription')" />

		<div class="citation-form__actions">
			<NcButton type="button" :disabled="saving || creatingSource" @click="emit('cancel')">
				Cancel
			</NcButton>
			<NcButton
				type="submit"
				variant="primary"
				:disabled="saving || creatingSource">
				{{ saving || creatingSource ? 'Saving…' : 'Save' }}
			</NcButton>
		</div>
	</form>
</template>

<style scoped>
.citation-form {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.citation-form__field {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.citation-form__error {
	margin: 0;
	font-size: 0.85rem;
	color: var(--color-text-error, var(--color-error));
}

.citation-form__actions {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	margin-top: 8px;
}
</style>
