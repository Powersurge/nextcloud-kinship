import axios from '@nextcloud/axios'
import { apiUrl } from './client'

export type AdminSettings = {
	gedcomImportEnabled: boolean
	extensionsEnabled: boolean
	privacyThreshold: number
}

export async function updateAdminSettings(
	settings: AdminSettings,
): Promise<AdminSettings> {
	const { data } = await axios.put<AdminSettings>(apiUrl('/admin/settings'), {
		settings,
	})
	return data
}
