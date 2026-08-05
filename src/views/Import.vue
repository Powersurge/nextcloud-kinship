<script setup lang="ts">
import { ref } from 'vue'
import { storeToRefs } from 'pinia'
import { NcButton, NcLoadingIcon, NcNoteCard } from '@nextcloud/vue'
import Upload from 'vue-material-design-icons/Upload.vue'
import { importGedcom } from '@/api/import.ts'
import { usePermissionsStore } from '@/stores/use-permissions-store.ts'

const { canEdit } = storeToRefs(usePermissionsStore())

const fileInput = ref<HTMLInputElement | null>(null)
const importing = ref(false)
const message = ref('')
const error = ref('')

function pickFile() {
	fileInput.value?.click()
}

async function onFileChange(event: Event) {
	const input = event.target as HTMLInputElement
	const file = input.files?.[0]
	if (!file) {
		return
	}

	importing.value = true
	message.value = ''
	error.value = ''

	try {
		const content = await file.text()
		const result = await importGedcom(content)
		const parts = [`${result.imported} people`]
		if (typeof result.relationships === 'number') {
			parts.push(`${result.relationships} relationships`)
		}
		if (typeof result.sources === 'number') {
			parts.push(`${result.sources} sources`)
		}
		if (typeof result.citations === 'number') {
			parts.push(`${result.citations} citations`)
		}
		message.value = `Imported ${parts.join(', ')}.`
	} catch (e) {
		error.value = 'Could not import the file. Please try again.'
		console.error('Failed to import GEDCOM', e)
	} finally {
		importing.value = false
		input.value = ''
	}
}
</script>

<template>
	<div class="import">
		<div class="import__header">
			<h2 class="import__title">
				Import GEDCOM
			</h2>
		</div>

		<div class="import__content">
			<p class="import__intro">
				Upload a GEDCOM (.ged) file to import people into Kinship.
			</p>

			<NcNoteCard v-if="!canEdit" type="warning">
				You do not have permission to import data.
			</NcNoteCard>

			<template v-else>
				<input
					ref="fileInput"
					type="file"
					accept=".ged,.gedcom"
					class="import__file-input"
					@change="onFileChange">

				<NcButton
					variant="primary"
					:disabled="importing"
					@click="pickFile">
					<template #icon>
						<NcLoadingIcon v-if="importing" :size="20" />
						<Upload v-else :size="20" />
					</template>
					Choose file
				</NcButton>
			</template>

			<NcNoteCard v-if="message" type="success">
				{{ message }}
			</NcNoteCard>

			<NcNoteCard v-if="error" type="error">
				{{ error }}
			</NcNoteCard>
		</div>
	</div>
</template>

<style scoped>
.import {
	display: flex;
	flex-direction: column;
}

.import__header {
	display: flex;
	gap: var(--default-grid-baseline);
	align-items: center;
	flex: 0 0;
	max-width: 100%;
	margin-block: var(--app-navigation-padding, 4px);
	margin-inline: calc(var(--default-clickable-area) + 2 * var(--app-navigation-padding, 4px)) var(--app-navigation-padding, 4px);
}

.import__title {
	margin: 0;
}

.import__content {
	display: flex;
	flex-direction: column;
	align-items: flex-start;
	gap: 12px;
	margin: 16px;
}

.import__intro {
	margin: 0;
	color: var(--color-text-maxcontrast);
}

.import__file-input {
	display: none;
}
</style>
