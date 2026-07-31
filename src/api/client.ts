import { generateUrl } from '@nextcloud/router'

/** Base path for Kinship REST API routes (see appinfo/routes.php). */
export const API_BASE = '/apps/kinship/api/v1'

/**
 * Build a Nextcloud app URL for an API path.
 * @param path - Path relative to API_BASE, with or without a leading slash
 */
export function apiUrl(path: string): string {
	const normalized = path.startsWith('/') ? path : `/${path}`
	return generateUrl(`${API_BASE}${normalized}`)
}
