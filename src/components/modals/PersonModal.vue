<script setup lang="ts">
import { computed, ref } from 'vue'
import { NcDialog, NcNoteCard } from '@nextcloud/vue'
import PersonForm from '../forms/PersonForm.vue'
import { createPerson, updatePerson } from '@/api/people'

const props = defineProps<{
	/** Controls visibility (use with v-model:open). */
	open: boolean
	/** Person being edited; null/undefined means create mode. */
	person?: Record<string, unknown> | null
}>()

const emit = defineEmits<{
	'update:open': [value: boolean]
	/** Emitted with the persisted person after a successful save. */
	saved: [person: Record<string, unknown>]
}>()

const saving = ref(false)
const error = ref('')

const isEdit = computed(() => Boolean(props.person?.id))
const title = computed(() => (isEdit.value ? 'Edit person' : 'New person'))

function close() {
	emit('update:open', false)
}

async function onSubmit(formData: Record<string, any>) {
	saving.value = true
	error.value = ''

	try {
		const person = isEdit.value
			? await updatePerson(props.person!.id as number, formData)
			: await createPerson(formData)

		emit('saved', person)
		close()
	} catch (e) {
		error.value = 'Could not save the person. Please try again.'
		console.error('Failed to save person', e)
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

		<div class="person-dialog-content">
			<PersonForm
				:person="person"
				:saving="saving"
				@submit="onSubmit"
				@cancel="close" />
		</div>
	</NcDialog>
</template>

<style>
.person-dialog-content {
	padding: 10px 0px;
}
</style>
