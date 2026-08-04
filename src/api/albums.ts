import axios from '@nextcloud/axios'
import type { Album, Media } from '@/types'
import { apiUrl } from './client'

export type AlbumsQueryParams = {
	ownerType: string
	ownerId: number
}

export type AlbumPayload = {
	title: string
	description?: string | null
	ownerType: string
	ownerId: number
	coverMediaId?: number | null
}

export async function fetchAlbums(params: AlbumsQueryParams): Promise<Album[]> {
	const { data } = await axios.get(apiUrl('/albums'), { params })
	return data.albums ?? []
}

export async function fetchAlbum(id: number): Promise<Album> {
	const { data } = await axios.get(apiUrl(`/albums/${id}`))
	return data
}

export async function fetchAlbumMedia(id: number): Promise<Media[]> {
	const { data } = await axios.get(apiUrl(`/albums/${id}/media`))
	return data.media ?? []
}

export async function createAlbum(payload: AlbumPayload): Promise<Album> {
	const { data } = await axios.post(apiUrl('/albums'), { data: payload })
	return data
}

export async function updateAlbum(id: number, payload: Partial<AlbumPayload>): Promise<Album> {
	const { data } = await axios.put(apiUrl(`/albums/${id}`), { data: payload })
	return data
}

export async function deleteAlbum(id: number): Promise<void> {
	await axios.delete(apiUrl(`/albums/${id}`))
}
