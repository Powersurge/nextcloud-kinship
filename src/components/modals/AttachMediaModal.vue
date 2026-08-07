<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { NcDialog, NcButton, NcTextField, NcCheckboxRadioSwitch, NcSelect } from '@nextcloud/vue'
import { storeToRefs } from 'pinia'
import { useMediaStore } from '@/stores/use-media-store.ts'
import { useAlbumStore } from '@/stores/use-album-store.ts'
import { createMedia, uploadMedia } from '@/api/media.ts'
import { getErrorMessage } from '@/utils/api-errors.ts'
import { showError } from '@nextcloud/dialogs'

const props = defineProps<{
	open: boolean
}>()

const emit = defineEmits<{
	'update:open': [value: boolean]
	saved: []
}>()

const mediaStore = useMediaStore()
const albumStore = useAlbumStore()
const { attachContext } = storeToRefs(mediaStore)
const { closeAttachModal, upsertMedia } = mediaStore

const caption = ref('')
const isPrimary = ref(false)
const albumId = ref<number | null>(null)
const saving = ref(false)
const error = ref('')

const albumOptions = computed(() => {
	const ctx = attachContext.value
	if (!ctx) {
		return []
	}
	return albumStore.getAlbums(ctx.subjectType, ctx.subjectId).map((album) => ({
		value: album.id,
		label: album.title,
	}))
})

const selectedAlbum = computed({
	get: () => albumOptions.value.find((o) => o.value === albumId.value) ?? null,
	set: (value: { value: number, label: string } | null) => {
		albumId.value = value?.value ?? null
	},
})

const canSetPrimary = computed(() => {
	const type = attachContext.value?.subjectType
	return type === 'person' || type === 'family' || type === 'event'
})

const pendingCount = computed(() => {
	const ctx = attachContext.value
	if (!ctx) {
		return 0
	}
	return (ctx.fileIds?.length ?? 0) + (ctx.files?.length ?? 0)
})

watch(
	() => props.open,
	(open) => {
		if (open) {
			caption.value = ''
			isPrimary.value = false
			albumId.value = attachContext.value?.albumId ?? null
			error.value = ''
		} else {
			closeAttachModal()
		}
	},
)

function close() {
	emit('update:open', false)
	closeAttachModal()
}

async function onSubmit() {
	const ctx = attachContext.value
	if (!ctx) {
		return
	}

	saving.value = true
	error.value = ''

	try {
		const setPrimary = canSetPrimary.value && isPrimary.value
		const meta = {
			subjectType: ctx.subjectType,
			subjectId: ctx.subjectId,
			albumId: albumId.value,
			caption: caption.value || null,
			isPrimary: setPrimary,
		}

		if (ctx.fileIds?.length) {
			for (const fileId of ctx.fileIds) {
				const media = await createMedia({
					fileId,
					...meta,
					isPrimary: setPrimary && fileId === ctx.fileIds[0],
				})
				upsertMedia(media)
			}
		}

		if (ctx.files?.length) {
			for (let i = 0; i < ctx.files.length; i++) {
				const media = await uploadMedia(ctx.files[i], {
					...meta,
					isPrimary: setPrimary && i === 0,
				})
				upsertMedia(media)
			}
		}

		emit('saved')
		close()
	} catch (e) {
		error.value = getErrorMessage(e, 'Could not attach media.')
		showError(error.value)
		console.error(e)
	} finally {
		saving.value = false
	}
}
</script>

<template>
	<NcDialog
		:open="open"
		name="Attach media"
		size="normal"
		:close-on-click-outside="!saving"
		@update:open="emit('update:open', $event)">
		<div class="attach-media-dialog">
			<p class="attach-media-dialog__count">
				{{ pendingCount }} file{{ pendingCount === 1 ? '' : 's' }} selected
			</p>

			<NcTextField
				v-model="caption"
				label="Caption"
				:disabled="saving" />

			<div
				v-if="albumOptions.length"
				class="attach-media-dialog__album">
				<label class="attach-media-dialog__label">Album (optional)</label>
				<NcSelect
					v-model="selectedAlbum"
					:options="albumOptions"
					input-label="Album"
					:clearable="true"
					:disabled="saving" />
			</div>

			<NcCheckboxRadioSwitch
				v-if="canSetPrimary"
				v-model="isPrimary"
				type="switch"
				:disabled="saving">
				Set as primary photo / cover
			</NcCheckboxRadioSwitch>

			<p
				v-if="error"
				class="attach-media-dialog__error">
				{{ error }}
			</p>

			<div class="attach-media-dialog__actions">
				<NcButton
					:disabled="saving"
					@click="close">
					Cancel
				</NcButton>
				<NcButton
					variant="primary"
					:disabled="saving || pendingCount === 0"
					@click="onSubmit">
					{{ saving ? 'Attaching…' : 'Attach' }}
				</NcButton>
			</div>
		</div>
	</NcDialog>
</template>

<style scoped>
.attach-media-dialog {
	display: flex;
	flex-direction: column;
	gap: 0.85rem;
	padding: 0.5rem 0 0.25rem;
}

.attach-media-dialog__count {
	margin: 0;
	font-size: 0.85rem;
	color: var(--color-text-maxcontrast);
}

.attach-media-dialog__label {
	display: block;
	margin-bottom: 0.25rem;
	font-size: 0.8rem;
	font-weight: 600;
	color: var(--color-text-maxcontrast);
}

.attach-media-dialog__error {
	margin: 0;
	color: var(--color-error);
	font-size: 0.85rem;
}

.attach-media-dialog__actions {
	display: flex;
	justify-content: flex-end;
	gap: 0.5rem;
	margin-top: 0.25rem;
}
</style>
