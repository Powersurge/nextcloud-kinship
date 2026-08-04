import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Album } from '@/types'
import { fetchAlbums } from '@/api/albums'

export type AlbumOwner = {
	ownerType: string
	ownerId: number
}

export type AlbumFormData = {
	id?: number
	title: string
	description: string | null
	ownerType: string
	ownerId: number
	coverMediaId?: number | null
}

function ownerKey(ownerType: string, ownerId: number): string {
	return `${ownerType}:${ownerId}`
}

export const useAlbumStore = defineStore('albumStore', () => {
	const albumsByOwner = ref<Record<string, Album[]>>({})
	const loading = ref(false)
	const showFormModal = ref(false)
	const albumForm = ref<AlbumFormData | Record<string, unknown>>({})
	const activeAlbumId = ref<number | null>(null)

	function getAlbums(ownerType: string, ownerId: number): Album[] {
		return albumsByOwner.value[ownerKey(ownerType, ownerId)] ?? []
	}

	async function loadForOwner(ownerType: string, ownerId: number) {
		const key = ownerKey(ownerType, ownerId)
		try {
			albumsByOwner.value[key] = await fetchAlbums({ ownerType, ownerId })
		} catch (e) {
			console.error('Failed to load albums', e)
			albumsByOwner.value[key] = []
		}
	}

	async function loadForOwners(owners: AlbumOwner[]) {
		const unique = new Map<string, AlbumOwner>()
		for (const owner of owners) {
			if (!owner.ownerType || !owner.ownerId) {
				continue
			}
			unique.set(ownerKey(owner.ownerType, owner.ownerId), owner)
		}

		loading.value = true
		try {
			await Promise.all(
				[...unique.values()].map((owner) =>
					loadForOwner(owner.ownerType, owner.ownerId),
				),
			)
		} finally {
			loading.value = false
		}
	}

	function reset() {
		albumsByOwner.value = {}
		loading.value = false
		activeAlbumId.value = null
	}

	function openAlbumForm(formData: AlbumFormData | Record<string, unknown>) {
		albumForm.value = formData
		showFormModal.value = true
	}

	function openCreateAlbumForm(ownerType: string, ownerId: number) {
		openAlbumForm({
			title: '',
			description: null,
			ownerType,
			ownerId,
			coverMediaId: null,
		})
	}

	function openEditAlbumForm(album: Album) {
		openAlbumForm({
			id: album.id,
			title: album.title,
			description: album.description,
			ownerType: album.ownerType,
			ownerId: album.ownerId,
			coverMediaId: album.coverMediaId,
		})
	}

	function upsertAlbum(album: Album) {
		const key = ownerKey(album.ownerType, album.ownerId)
		const current = albumsByOwner.value[key] ?? []
		const index = current.findIndex((item) => item.id === album.id)
		if (index >= 0) {
			const next = [...current]
			next[index] = album
			albumsByOwner.value[key] = next
		} else {
			albumsByOwner.value[key] = [...current, album].sort((a, b) =>
				a.title.localeCompare(b.title),
			)
		}
	}

	function removeAlbum(album: Album) {
		const key = ownerKey(album.ownerType, album.ownerId)
		albumsByOwner.value[key] = (albumsByOwner.value[key] ?? [])
			.filter((item) => item.id !== album.id)
		if (activeAlbumId.value === album.id) {
			activeAlbumId.value = null
		}
	}

	return {
		albumsByOwner,
		loading,
		showFormModal,
		albumForm,
		activeAlbumId,
		getAlbums,
		loadForOwner,
		loadForOwners,
		reset,
		openAlbumForm,
		openCreateAlbumForm,
		openEditAlbumForm,
		upsertAlbum,
		removeAlbum,
	}
})
