import axios from '@nextcloud/axios'
import type { Media } from '@/types'
import { apiUrl } from './client'

export type MediaQueryParams = {
	subjectType: string
	subjectId: number
	/** Omit = all; 0 = loose (no album); >0 = that album */
	albumId?: number
}

export type MediaPayload = {
	fileId: number
	subjectType: string
	subjectId: number
	albumId?: number | null
	caption?: string | null
	isPrimary?: boolean
	sortOrder?: number
}

export async function fetchMedia(params: MediaQueryParams): Promise<Media[]> {
	const { data } = await axios.get(apiUrl('/media'), { params })
	return data.media ?? []
}

export async function createMedia(payload: MediaPayload): Promise<Media> {
	const { data } = await axios.post(apiUrl('/media'), { data: payload })
	return data
}

export async function updateMedia(id: number, payload: Partial<MediaPayload>): Promise<Media> {
	const { data } = await axios.put(apiUrl(`/media/${id}`), { data: payload })
	return data
}

export async function deleteMedia(id: number): Promise<void> {
	await axios.delete(apiUrl(`/media/${id}`))
}

export async function uploadMedia(
	file: File,
	meta: {
		subjectType: string
		subjectId: number
		albumId?: number | null
		caption?: string | null
		isPrimary?: boolean
	},
): Promise<Media> {
	const form = new FormData()
	form.append('file', file)
	form.append('subjectType', meta.subjectType)
	form.append('subjectId', String(meta.subjectId))
	if (meta.albumId != null) {
		form.append('albumId', String(meta.albumId))
	}
	if (meta.caption != null && meta.caption !== '') {
		form.append('caption', meta.caption)
	}
	if (meta.isPrimary) {
		form.append('isPrimary', '1')
	}

	const { data } = await axios.post(apiUrl('/media/upload'), form)
	return data
}
