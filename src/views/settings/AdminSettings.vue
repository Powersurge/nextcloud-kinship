<script setup lang="ts">
import { ref } from 'vue'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { NcSettingsSection, NcCheckboxRadioSwitch, NcNoteCard } from '@nextcloud/vue'

interface AdminSettingsState {
	gedcomImportEnabled: boolean
	extensionsEnabled: boolean
}

const settings = ref<AdminSettingsState>(
	loadState<AdminSettingsState>('kinship', 'admin-settings', {
		gedcomImportEnabled: true,
		extensionsEnabled: false,
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

async function updateSetting<K extends keyof AdminSettingsState>(
	key: K,
	value: AdminSettingsState[K],
): Promise<void> {
	settings.value[key] = value
	await save()
}
</script>

<template>
	<div class="kinship-admin-settings">
		<NcNoteCard v-if="error" type="error">
			{{ error }}
		</NcNoteCard>

		<NcSettingsSection
			name="Import"
			description="Control whether users can import genealogy data into Kinship.">
			<NcCheckboxRadioSwitch
				v-model="settings.gedcomImportEnabled"
				type="switch"
				@update:model-value="updateSetting('gedcomImportEnabled', $event)">
				Allow GEDCOM import
			</NcCheckboxRadioSwitch>
		</NcSettingsSection>

		<NcSettingsSection
			name="Extensions"
			description="Enable third-party Kinship extensions registered on this server.">
			<NcCheckboxRadioSwitch
				v-model="settings.extensionsEnabled"
				type="switch"
				@update:model-value="updateSetting('extensionsEnabled', $event)">
				Enable extensions
			</NcCheckboxRadioSwitch>
		</NcSettingsSection>
	</div>
</template>

<style scoped>
.kinship-admin-settings {
	display: flex;
	flex-direction: column;
	gap: 16px;
	padding: 24px;
}
</style>
