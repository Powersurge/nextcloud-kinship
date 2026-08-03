import axios from '@nextcloud/axios'
import { apiUrl } from './client'

export type GedcomImportResult = {
	imported: number
	relationships?: number
	sources?: number
	citations?: number
	[key: string]: unknown
}

export async function importGedcom(content: string): Promise<GedcomImportResult> {
	const { data } = await axios.post(apiUrl('/import/gedcom'), { content })
	return data
}
