<script setup lang="ts">
import { computed, watch } from 'vue'
import { NcActions, NcActionButton, NcButton } from '@nextcloud/vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Upload from 'vue-material-design-icons/Upload.vue'
import FolderPlus from 'vue-material-design-icons/FolderPlus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Star from 'vue-material-design-icons/Star.vue'
import StarOutline from 'vue-material-design-icons/StarOutline.vue'
import ImageMultiple from 'vue-material-design-icons/ImageMultiple.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import EyeOutline from 'vue-material-design-icons/EyeOutline.vue'
import Download from 'vue-material-design-icons/Download.vue'
import { storeToRefs } from 'pinia'
import { useMediaStore } from '@/stores/use-media-store.ts'
import { useAlbumStore } from '@/stores/use-album-store.ts'
import { deleteAlbum as deleteAlbumApi } from '@/api/albums.ts'
import { deleteMedia as deleteMediaApi, updateMedia } from '@/api/media.ts'
import type { Album, Media } from '@/types'
import { mediaPreviewUrl } from '@/utils/mediaPreview.ts'
import { pickLocalFiles, pickMediaFileIds } from '@/utils/mediaPicker.ts'
import { canOpenInViewer, downloadMediaFile, openMediaInViewer } from '@/utils/mediaViewer.ts'
import { showConfirmation, showError } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'

const props = withDefaults(defineProps<{
	subjectType: string
	subjectId: number
	showPrimaryAction?: boolean
}>(), {
	showPrimaryAction: true,
})

const mediaStore = useMediaStore()
const albumStore = useAlbumStore()
const { mediaBySubject } = storeToRefs(mediaStore)
const { albumsByOwner, activeAlbumId } = storeToRefs(albumStore)
const { openAttachModal, removeMedia, upsertMedia, loadForSubject } = mediaStore
const { openCreateAlbumForm, openEditAlbumForm, removeAlbum, loadForOwner } = albumStore

const albums = computed(() =>
	albumsByOwner.value[`${props.subjectType}:${props.subjectId}`] ?? [],
)

const activeAlbum = computed(() =>
	albums.value.find((album) => album.id === activeAlbumId.value) ?? null,
)

const targetAlbumId = computed(() => activeAlbumId.value)

const mediaItems = computed(() => {
	const all = mediaBySubject.value[`${props.subjectType}:${props.subjectId}`] ?? []
	if (activeAlbumId.value) {
		return all.filter((item) => item.albumId === activeAlbumId.value)
	}
	return all.filter((item) => item.albumId == null)
})

const isEmpty = computed(() => {
	if (activeAlbum.value) {
		return mediaItems.value.length === 0
	}
	return albums.value.length === 0 && mediaItems.value.length === 0
})

watch(
	() => [props.subjectType, props.subjectId] as const,
	([subjectType, subjectId]) => {
		if (subjectType && subjectId) {
			activeAlbumId.value = null
			loadForOwner(subjectType, subjectId)
			loadForSubject(subjectType, subjectId)
		}
	},
	{ immediate: true },
)

function openCreateAlbum() {
	openCreateAlbumForm(props.subjectType, props.subjectId)
}

function openAlbum(album: Album) {
	activeAlbumId.value = album.id
}

function backToRoot() {
	activeAlbumId.value = null
}

async function onPick() {
	const fileIds = await pickMediaFileIds({ multiSelect: true })
	if (!fileIds?.length) {
		return
	}
	openAttachModal({
		subjectType: props.subjectType,
		subjectId: props.subjectId,
		albumId: targetAlbumId.value,
		fileIds,
	})
}

async function onUpload() {
	const files = await pickLocalFiles({ multiple: true })
	if (!files?.length) {
		return
	}
	openAttachModal({
		subjectType: props.subjectType,
		subjectId: props.subjectId,
		albumId: targetAlbumId.value,
		files,
	})
}

async function onDeleteAlbum(album: Album) {
	const confirmed = await showConfirmation({
		name: 'Delete album?',
		text: 'Media in this album will be unlinked from Kinship. Files in Nextcloud are kept.',
		labelReject: 'Cancel',
		severity: 'error',
	})
	if (!confirmed) {
		return
	}
	try {
		await deleteAlbumApi(album.id)
		removeAlbum(album)
		await loadForSubject(props.subjectType, props.subjectId)
	} catch (e) {
		showError('Failed to delete album')
		console.error(e)
	}
}

async function onDeleteMedia(media: Media) {
	const confirmed = await showConfirmation({
		name: 'Remove media?',
		text: 'This unlinks the file from Kinship. The file in Nextcloud is kept.',
		labelReject: 'Cancel',
		severity: 'error',
	})
	if (!confirmed) {
		return
	}
	try {
		await deleteMediaApi(media.id)
		removeMedia(media)
	} catch (e) {
		showError('Failed to remove media')
		console.error(e)
	}
}

async function onTogglePrimary(media: Media) {
	try {
		const updated = await updateMedia(media.id, { isPrimary: !media.isPrimary })
		upsertMedia(updated)
	} catch (e) {
		showError('Failed to update primary photo')
		console.error(e)
	}
}

function onOpenMedia(media: Media) {
	if (!media.file) {
		showError('File is unavailable')
		return
	}
	openMediaInViewer(media, mediaItems.value)
}

function onDownloadMedia(media: Media) {
	downloadMediaFile(media.file)
}

function canPreview(media: Media): boolean {
	return canOpenInViewer(media.file?.mime)
}

function coverUrl(album: Album): string | null {
	const fileId = album.coverFile?.fileId
	return fileId
		? mediaPreviewUrl(fileId, 256)
		: generateUrl('/core/mimeicon?mime=folder')
}

function thumbUrl(media: Media): string {
	const mime = media.file?.mime ?? ''

	if (media.file?.hasPreview) {
		return mediaPreviewUrl(media.fileId, 256)
	}

	return generateUrl(`/core/mimeicon?mime=${mime}`, {
		mime,
	})
}
</script>

<template>
	<section class="media-section">
		<div class="media-section__toolbar">
			<div class="media-section__nav">
				<template v-if="activeAlbum">
					<NcButton variant="tertiary-no-background" @click="backToRoot">
						<template #icon>
							<ArrowLeft :size="16" />
						</template>
					</NcButton>
					<span class="media-section__title">{{ activeAlbum.title }}</span>
					<NcActions>
						<NcActionButton close-after-click @click="openEditAlbumForm(activeAlbum)">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Edit album
						</NcActionButton>
						<NcActionButton close-after-click @click="onDeleteAlbum(activeAlbum)">
							<template #icon>
								<Delete :size="20" />
							</template>
							Delete album
						</NcActionButton>
					</NcActions>
				</template>
			</div>

			<div class="media-section__actions">
				<NcButton
					v-if="!activeAlbum"
					size="small"
					@click="openCreateAlbum">
					<template #icon>
						<FolderPlus :size="16" />
					</template>
					New album
				</NcButton>
				<NcButton size="small" @click="onPick">
					<template #icon>
						<Plus :size="16" />
					</template>
					Attach
				</NcButton>
				<NcButton size="small" @click="onUpload">
					<template #icon>
						<Upload :size="16" />
					</template>
					Upload
				</NcButton>
			</div>
		</div>

		<p
			v-if="activeAlbum?.description"
			class="media-section__description">
			{{ activeAlbum.description }}
		</p>

		<p
			v-if="isEmpty"
			class="media-section__empty">
			{{ activeAlbum ? 'No media in this album yet' : 'No albums or media yet' }}
		</p>

		<ul
			v-else
			class="media-section__grid">
			<template v-if="!activeAlbum">
				<li
					v-for="album in albums"
					:key="`album-${album.id}`"
					class="media-section__tile media-section__tile--album"
					@click="openAlbum(album)">
					<div class="media-section__thumb">
						<img
							v-if="coverUrl(album)"
							:src="coverUrl(album)"
							:alt="album.title">
						<ImageMultiple
							v-else
							:size="32" />
						<span class="media-section__badge media-section__badge--album">
							Album
						</span>
					</div>
					<div class="media-section__meta">
						<div class="media-section__caption">
							{{ album.title }}
						</div>
						<NcActions @click.stop>
							<NcActionButton close-after-click @click="openEditAlbumForm(album)">
								<template #icon>
									<Pencil :size="20" />
								</template>
								Edit
							</NcActionButton>
							<NcActionButton close-after-click @click="onDeleteAlbum(album)">
								<template #icon>
									<Delete :size="20" />
								</template>
								Delete
							</NcActionButton>
						</NcActions>
					</div>
				</li>
			</template>

			<li
				v-for="media in mediaItems"
				:key="`media-${media.id}`"
				class="media-section__tile"
				:class="{ 'media-section__tile--primary': media.isPrimary }">
				<button
					type="button"
					class="media-section__thumb media-section__thumb--button"
					:title="canPreview(media) ? 'Open preview' : 'Open file'"
					@click="onOpenMedia(media)">
					<img
						v-if="thumbUrl(media)"
						:src="thumbUrl(media)"
						:alt="media.caption || media.file?.name || 'Media'">
					<span v-else class="media-section__fallback">
						{{ media.file?.name ?? `File #${media.fileId}` }}
					</span>
					<span
						v-if="media.isPrimary"
						class="media-section__badge">
						Primary
					</span>
				</button>
				<div class="media-section__meta">
					<div class="media-section__caption">
						{{ media.caption || media.file?.name || `File #${media.fileId}` }}
					</div>
					<NcActions>
						<NcActionButton close-after-click @click="onOpenMedia(media)">
							<template #icon>
								<EyeOutline :size="20" />
							</template>
							{{ canPreview(media) ? 'Open' : 'Preview' }}
						</NcActionButton>
						<NcActionButton close-after-click @click="onDownloadMedia(media)">
							<template #icon>
								<Download :size="20" />
							</template>
							Download
						</NcActionButton>
						<NcActionButton
							v-if="showPrimaryAction"
							close-after-click
							@click="onTogglePrimary(media)">
							<template #icon>
								<Star v-if="media.isPrimary" :size="20" />
								<StarOutline v-else :size="20" />
							</template>
							{{ media.isPrimary ? 'Unset primary' : 'Set primary' }}
						</NcActionButton>
						<NcActionButton close-after-click @click="onDeleteMedia(media)">
							<template #icon>
								<Delete :size="20" />
							</template>
							Remove
						</NcActionButton>
					</NcActions>
				</div>
			</li>
		</ul>
	</section>
</template>

<style scoped>
.media-section {
	display: flex;
	flex-direction: column;
	gap: 0.6rem;
}

.media-section__toolbar {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 0.5rem;
	flex-wrap: wrap;
}

.media-section__nav {
	display: flex;
	align-items: center;
	gap: 0.4rem;
	min-width: 0;
	flex: 1;
}

.media-section__title {
	font-size: 0.95rem;
	font-weight: 600;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.media-section__actions {
	display: flex;
	flex-wrap: wrap;
	gap: 0.35rem;
}

.media-section__description {
	margin: 0;
	font-size: 0.8rem;
	color: var(--color-text-maxcontrast);
}

.media-section__empty {
	margin: 0;
	font-size: 0.85rem;
	color: var(--color-text-maxcontrast);
}

.media-section__grid {
	list-style: none;
	margin: 0;
	padding: 0;
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
	gap: 0.5rem;
}

.media-section__tile {
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
	background: var(--color-background-dark);
	overflow: hidden;
}

.media-section__tile--album {
	cursor: pointer;
}

.media-section__tile--album:hover,
.media-section__tile--primary {
	border-color: var(--color-primary);
}

.media-section__thumb {
	position: relative;
	aspect-ratio: 1;
	background: var(--color-background-hover);
	display: flex;
	align-items: center;
	justify-content: center;
	color: var(--color-text-maxcontrast);
	overflow: hidden;
}

.media-section__thumb--button {
	width: 100%;
	padding: 0;
	border: none;
	cursor: pointer;
	color: inherit;
}

.media-section__thumb--button:hover {
	opacity: 0.92;
}

.media-section__thumb--button:focus-visible {
	outline: 2px solid var(--color-primary);
	outline-offset: -2px;
}

.media-section__thumb img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.media-section__fallback {
	padding: 0.5rem;
	font-size: 0.75rem;
	text-align: center;
	word-break: break-word;
}

.media-section__badge {
	position: absolute;
	top: 0.35rem;
	left: 0.35rem;
	padding: 0.1rem 0.4rem;
	border-radius: var(--border-radius-pill);
	background: var(--color-primary);
	color: var(--color-primary-text);
	font-size: 0.65rem;
	font-weight: 600;
}

.media-section__badge--album {
	background: var(--color-background-darker, var(--color-main-background));
	color: var(--color-main-text);
	border: 1px solid var(--color-border);
}

.media-section__meta {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 0.25rem;
	padding: 0.35rem 0.4rem;
}

.media-section__caption {
	font-size: 0.75rem;
	line-height: 1.3;
	overflow: hidden;
	display: -webkit-box;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
}
</style>
