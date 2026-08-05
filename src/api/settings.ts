import axios from '@nextcloud/axios'
import { apiUrl } from './client'

export type PlaceLookupProvider = 'none' | 'nominatim' | 'google'

export type DefaultRole = 'none' | 'viewer' | 'contributor' | 'editor'

export type AdminSettings = {
	gedcomImportEnabled: boolean
	extensionsEnabled: boolean
	privacyThreshold: number
	placeLookupProvider: PlaceLookupProvider
	nominatimBaseUrl: string
	googleMapsApiKey: string
	defaultRole: DefaultRole
	editorCanDelete: boolean
	editorCanAcceptClaims: boolean
}

export async function updateAdminSettings(
	settings: AdminSettings,
): Promise<AdminSettings> {
	const { data } = await axios.put<AdminSettings>(apiUrl('/admin/settings'), {
		settings,
	})
	return data
}
