<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { NcDialog, NcNoteCard } from '@nextcloud/vue'
import AlbumForm from '../forms/AlbumForm.vue'
import { type AlbumPayload, createAlbum, updateAlbum } from '@/api/albums.ts'
import { getErrorMessage, getFieldErrors, type FieldErrors } from '@/utils/api-errors.ts'
import { useAlbumStore } from '@/stores/use-album-store.ts'
import type { Album } from '@/types'

const props = defineProps<{
	open: boolean
	album?: Record<string, unknown> | null
}>()

const emit = defineEmits<{
	'update:open': [value: boolean]
	saved: [album: Album]
}>()

const { upsertAlbum } = useAlbumStore()

const saving = ref(false)
const error = ref('')
const fieldErrors = ref<FieldErrors | null>(null)

const isEdit = computed(() => Boolean(props.album?.id))
const title = computed(() => (isEdit.value ? 'Edit album' : 'New album'))

watch(
	() => props.open,
	(open) => {
		if (!open) {
			error.value = ''
			fieldErrors.value = null
		}
	},
)

function close() {
	emit('update:open', false)
}

async function onSubmit(data: Record<string, unknown>) {
	saving.value = true
	error.value = ''
	fieldErrors.value = null

	const payload = {
		title: data.title,
		description: data.description ?? null,
		ownerType: props.album?.ownerType,
		ownerId: props.album?.ownerId,
		coverMediaId: data.coverMediaId ?? null,
	}

	try {
		const album = isEdit.value
			? await updateAlbum(props.album!.id as number, payload)
			: await createAlbum(payload as AlbumPayload)

		upsertAlbum(album)
		emit('saved', album)
		close()
	} catch (e) {
		const errors = getFieldErrors(e)
		if (errors) {
			fieldErrors.value = errors
			error.value = ''
		} else {
			error.value = getErrorMessage(e, 'Could not save the album. Please try again.')
		}
		console.error('Failed to save album', e)
	} finally {
		saving.value = false
	}
}
</script>

<template>
	<NcDialog
		:open="open"
		:name="title"
		size="normal"
		:close-on-click-outside="!saving"
		@update:open="emit('update:open', $event)">
		<NcNoteCard v-if="error" type="error">
			{{ error }}
		</NcNoteCard>

		<div class="album-dialog-content">
			<AlbumForm
				:album="album"
				:saving="saving"
				:field-errors="fieldErrors"
				@submit="onSubmit"
				@cancel="close" />
		</div>
	</NcDialog>
</template>

<style>
.album-dialog-content {
	padding: 10px 0;
}
</style>
