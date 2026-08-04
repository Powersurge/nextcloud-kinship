import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Media } from '@/types'
import { fetchMedia } from '@/api/media'

export type MediaSubject = {
	subjectType: string
	subjectId: number
}

function subjectKey(subjectType: string, subjectId: number): string {
	return `${subjectType}:${subjectId}`
}

export const useMediaStore = defineStore('mediaStore', () => {
	const mediaBySubject = ref<Record<string, Media[]>>({})
	const loading = ref(false)
	const showAttachModal = ref(false)
	const attachContext = ref<{
		subjectType: string
		subjectId: number
		albumId?: number | null
		fileIds?: number[]
		files?: File[]
	} | null>(null)

	function getMedia(subjectType: string, subjectId: number): Media[] {
		return mediaBySubject.value[subjectKey(subjectType, subjectId)] ?? []
	}

	function getLooseMedia(subjectType: string, subjectId: number): Media[] {
		return getMedia(subjectType, subjectId).filter((item) => item.albumId == null)
	}

	function getAlbumMedia(subjectType: string, subjectId: number, albumId: number): Media[] {
		return getMedia(subjectType, subjectId).filter((item) => item.albumId === albumId)
	}

	function mediaCount(subjectType: string, subjectId: number): number {
		return getMedia(subjectType, subjectId).length
	}

	async function loadForSubject(subjectType: string, subjectId: number) {
		const key = subjectKey(subjectType, subjectId)
		try {
			mediaBySubject.value[key] = await fetchMedia({ subjectType, subjectId })
		} catch (e) {
			console.error('Failed to load media', e)
			mediaBySubject.value[key] = []
		}
	}

	async function loadForSubjects(subjects: MediaSubject[]) {
		const unique = new Map<string, MediaSubject>()
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
		mediaBySubject.value = {}
		loading.value = false
	}

	function openAttachModal(context: NonNullable<typeof attachContext.value>) {
		attachContext.value = context
		showAttachModal.value = true
	}

	function closeAttachModal() {
		showAttachModal.value = false
		attachContext.value = null
	}

	function upsertMedia(media: Media) {
		const key = subjectKey(media.subjectType, media.subjectId)
		const current = mediaBySubject.value[key] ?? []
		const index = current.findIndex((item) => item.id === media.id)
		let next: Media[]
		if (index >= 0) {
			next = [...current]
			next[index] = media
		} else {
			next = [...current, media]
		}
		if (media.isPrimary) {
			next = next.map((item) =>
				item.id === media.id ? item : { ...item, isPrimary: false },
			)
		}
		mediaBySubject.value[key] = next
	}

	function removeMedia(media: Media) {
		const key = subjectKey(media.subjectType, media.subjectId)
		mediaBySubject.value[key] = (mediaBySubject.value[key] ?? [])
			.filter((item) => item.id !== media.id)
	}

	return {
		mediaBySubject,
		loading,
		showAttachModal,
		attachContext,
		getMedia,
		getLooseMedia,
		getAlbumMedia,
		mediaCount,
		loadForSubject,
		loadForSubjects,
		reset,
		openAttachModal,
		closeAttachModal,
		upsertMedia,
		removeMedia,
	}
})
