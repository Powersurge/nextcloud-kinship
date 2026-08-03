<script setup lang="ts">
import { computed, ref } from 'vue'
import { NcDialog, NcNoteCard } from '@nextcloud/vue'
import RelationshipForm from '../forms/RelationshipForm.vue'
import CitationList from '@/components/citations/CitationList.vue'
import { createRelationship, updateRelationship } from '@/api/relationships.ts'

const props = defineProps<{
	/** Controls visibility (use with v-model:open). */
	open: boolean
	/** Relationship being edited; null/undefined means create mode. */
	relationship?: Record<string, any> | null
	/** Pre-fill Person A when creating from a person context. */
	defaultPersonA?: number | null
	/** Pre-fill relationship type when creating from a person context. */
	defaultType?: string | null
}>()

const emit = defineEmits<{
	'update:open': [value: boolean]
	saved: [relationship: Record<string, unknown>]
}>()

const saving = ref(false)
const error = ref('')

const isEdit = computed(() => Boolean(props.relationship?.id))
const title = computed(() => (isEdit.value ? 'Edit relationship' : 'New relationship'))
const relationshipId = computed(() =>
	typeof props.relationship?.id === 'number' ? props.relationship.id : null,
)

function close() {
	emit('update:open', false)
}

async function onSubmit(data: Record<string, unknown>) {
	saving.value = true
	error.value = ''

	try {
		const relationship = isEdit.value
			? await updateRelationship(props.relationship!.id as number, data)
			: await createRelationship(data)

		emit('saved', relationship)
		close()
	} catch (e) {
		error.value = 'Could not save the relationship. Please try again.'
		console.error('Failed to save relationship', e)
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

		<div class="rel-dialog-content">
			<RelationshipForm
				:relationship="relationship"
				:default-person-a="defaultPersonA"
				:default-type="defaultType"
				:saving="saving"
				@submit="onSubmit"
				@cancel="close" />

			<CitationList
				v-if="relationshipId"
				class="rel-dialog-citations"
				subject-type="relationship"
				:subject-id="relationshipId" />
		</div>
	</NcDialog>
</template>

<style>
.rel-dialog-content {
	padding: 10px 0px;
}

.rel-dialog-citations {
	margin-top: 1rem;
	padding-top: 0.75rem;
	border-top: 1px solid var(--color-border);
}
</style>
