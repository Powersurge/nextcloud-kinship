<script setup lang="ts">
import { computed, ref } from 'vue'
import { NcDialog, NcNoteCard } from '@nextcloud/vue'
import FamilyForm from '../forms/FamilyForm.vue'
import { createFamily, updateFamily } from '@/api/families.ts'

const props = defineProps<{
	/** Controls visibility (use with v-model:open). */
	open: boolean
	/** Family being edited; null/undefined means create mode. */
	family?: Record<string, unknown> | null
}>()

const emit = defineEmits<{
	'update:open': [value: boolean]
	/** Emitted with the persisted family after a successful save. */
	saved: [family: Record<string, unknown>]
}>()

const saving = ref(false)
const error = ref('')

const isEdit = computed(() => Boolean(props.family?.id))
const title = computed(() => (isEdit.value ? 'Edit family' : 'New family'))

function close() {
	emit('update:open', false)
}

async function onSubmit(data: Record<string, unknown>) {
	saving.value = true
	error.value = ''

	try {
		const family = isEdit.value
			? await updateFamily(props.family!.id as number, data)
			: await createFamily(data)

		emit('saved', family)
		close()
	} catch (e) {
		error.value = 'Could not save the family. Please try again.'
		console.error('Failed to save family', e)
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

		<div class="family-dialog-content">
			<FamilyForm
				:family="family"
				:saving="saving"
				@submit="onSubmit"
				@cancel="close" />
		</div>
	</NcDialog>
</template>

<style>
.family-dialog-content {
	padding: 10px 0px;
}
</style>
