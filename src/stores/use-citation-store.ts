import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Citation } from '@/types'
import { fetchCitations } from '@/api/citations'

export type CitationSubject = {
	subjectType: string
	subjectId: number
}

export type CitationFormData = {
	id?: number
	sourceId: number | null
	subjectType: string
	subjectId: number
	detail: string | null
	transcription: string | null
	confidence: string | null
	accessedDate: string | null
}

function subjectKey(subjectType: string, subjectId: number): string {
	return `${subjectType}:${subjectId}`
}

export const useCitationStore = defineStore('citationStore', () => {
	const citationsBySubject = ref<Record<string, Citation[]>>({})
	const loading = ref(false)
	const showFormModal = ref(false)
	const citationForm = ref<CitationFormData | Record<string, unknown>>({})

	function getCitations(subjectType: string, subjectId: number): Citation[] {
		return citationsBySubject.value[subjectKey(subjectType, subjectId)] ?? []
	}

	function citationCount(subjectType: string, subjectId: number): number {
		return getCitations(subjectType, subjectId).length
	}

	async function loadForSubject(subjectType: string, subjectId: number) {
		const key = subjectKey(subjectType, subjectId)
		try {
			citationsBySubject.value[key] = await fetchCitations({ subjectType, subjectId })
		} catch (e) {
			console.error('Failed to load citations', e)
			citationsBySubject.value[key] = []
		}
	}

	async function loadForSubjects(subjects: CitationSubject[]) {
		const unique = new Map<string, CitationSubject>()
		for (const subject of subjects) {
			if (!subject.subjectType || !subject.subjectId) {
				continue
			}
			unique.set(subjectKey(subject.subjectType, subject.subjectId), subject)
		}

		loading.value = true
		try {
			await Promise.all(
				[...unique.values()].map((subject) =>
					loadForSubject(subject.subjectType, subject.subjectId),
				),
			)
		} finally {
			loading.value = false
		}
	}

	function reset() {
		citationsBySubject.value = {}
		loading.value = false
	}

	function openCitationForm(formData: CitationFormData | Record<string, unknown>) {
		citationForm.value = formData
		showFormModal.value = true
	}

	function openCreateCitationForm(subjectType: string, subjectId: number) {
		openCitationForm({
			sourceId: null,
			subjectType,
			subjectId,
			detail: null,
			transcription: null,
			confidence: null,
			accessedDate: null,
		})
	}

	function openEditCitationForm(citation: Citation) {
		openCitationForm({
			id: citation.id,
			sourceId: citation.sourceId,
			subjectType: citation.subjectType,
			subjectId: citation.subjectId,
			detail: citation.detail,
			transcription: citation.transcription,
			confidence: citation.confidence,
			accessedDate: citation.accessedDate,
		})
	}

	function upsertCitation(citation: Citation) {
		const key = subjectKey(citation.subjectType, citation.subjectId)
		const current = citationsBySubject.value[key] ?? []
		const index = current.findIndex((item) => item.id === citation.id)
		if (index >= 0) {
			const next = [...current]
			next[index] = citation
			citationsBySubject.value[key] = next
		} else {
			citationsBySubject.value[key] = [...current, citation]
		}
	}

	function removeCitation(citation: Citation) {
		const key = subjectKey(citation.subjectType, citation.subjectId)
		citationsBySubject.value[key] = (citationsBySubject.value[key] ?? [])
			.filter((item) => item.id !== citation.id)
	}

	return {
		citationsBySubject,
		loading,
		showFormModal,
		citationForm,
		getCitations,
		citationCount,
		loadForSubject,
		loadForSubjects,
		reset,
		openCitationForm,
		openCreateCitationForm,
		openEditCitationForm,
		upsertCitation,
		removeCitation,
	}
})
