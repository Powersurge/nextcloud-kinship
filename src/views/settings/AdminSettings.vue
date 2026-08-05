<script setup lang="ts">
import { computed, ref } from 'vue'
import { loadState } from '@nextcloud/initial-state'
import { NcNoteCard, NcPasswordField, NcSelect, NcSettingsSection, NcTextField } from '@nextcloud/vue'
import { type AdminSettings, type PlaceLookupProvider, updateAdminSettings } from '@/api/settings.ts'
import PermissionSettings from '@/views/settings/PermissionSettings.vue'

type ProviderOption = {
	value: PlaceLookupProvider
	label: string
}

const settings = ref<AdminSettings>(
	loadState<AdminSettings>('kinship', 'admin-settings', {
		gedcomImportEnabled: true,
		extensionsEnabled: false,
		privacyThreshold: 110,
		placeLookupProvider: 'none',
		nominatimBaseUrl: 'https://nominatim.openstreetmap.org',
		googleMapsApiKey: '',
		defaultRole: 'viewer',
		editorCanDelete: true,
		editorCanAcceptClaims: true,
	}),
)
const error = ref('')

const providerOptions: ProviderOption[] = [
	{ value: 'none', label: 'None (local places only)' },
	{ value: 'nominatim', label: 'Nominatim (OpenStreetMap)' },
	{ value: 'google', label: 'Google Maps' },
]

const selectedProvider = computed({
	get: () => providerOptions.find((option) => option.value === settings.value.placeLookupProvider)
		?? providerOptions[0],
	set: (option: ProviderOption) => {
		settings.value.placeLookupProvider = option.value
		save()
	},
})

async function save(): Promise<void> {
	error.value = ''

	try {
		settings.value = await updateAdminSettings(settings.value)
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

		<PermissionSettings
			v-model="settings"
			@save="save" />

		<NcSettingsSection
			name="Place lookup"
			description="Optionally look up addresses with Nominatim or Google Maps when searching for places. Local places are always included.">
			<NcSelect
				v-model="selectedProvider"
				input-label="Geocoding provider"
				label="label"
				:options="providerOptions"
				:clearable="false" />

			<NcTextField
				v-if="settings.placeLookupProvider === 'nominatim'"
				v-model="settings.nominatimBaseUrl"
				class="admin-settings__field"
				label="Nominatim base URL"
				placeholder="https://nominatim.openstreetmap.org"
				@blur="save" />

			<NcPasswordField
				v-if="settings.placeLookupProvider === 'google'"
				v-model="settings.googleMapsApiKey"
				class="admin-settings__field"
				label="Google Maps API key"
				:as-text="true"
				autocomplete="off"
				@blur="save" />
		</NcSettingsSection>
	</div>
</template>

<style scoped>
.admin-settings__field {
	margin-top: 12px;
}
</style>
