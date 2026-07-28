<script setup lang="ts">
import { ref } from 'vue'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { NcSettingsSection, NcNoteCard, NcTextField } from '@nextcloud/vue'

interface AdminSettingsState {
	gedcomImportEnabled: boolean
	extensionsEnabled: boolean
	privacyThreshold: number
}

const settings = ref<AdminSettingsState>(
	loadState<AdminSettingsState>('kinship', 'admin-settings', {
		gedcomImportEnabled: true,
		extensionsEnabled: false,
		privacyThreshold: 110,
	}),
)
const error = ref('')

async function save(): Promise<void> {
	error.value = ''

	try {
		const response = await axios.put<AdminSettingsState>(
			generateUrl('/apps/kinship/api/v1/admin/settings'),
			{
				settings: settings.value,
			},
		)
		settings.value = response.data
	} catch (e) {
		error.value = 'Could not save settings. Please try again.'
		console.error('Failed to save admin settings', e)
	}
}
</script>

<template>
	<div>
		<div class="!px-8 pt-4">
			<NcNoteCard v-if="error" type="error">
				{{ error }}
			</NcNoteCard>
		</div>

		<NcSettingsSection
			name="Privacy"
			description="Controls how long people with an unknown death date are treated as living for privacy purposes.">
			<NcTextField
				v-model="settings.privacyThreshold"
				label="Privacy threshold (years)"
				type="number"
				min="1"
				max="200"
				@blur="save" />
		</NcSettingsSection>
	</div>
</template>
