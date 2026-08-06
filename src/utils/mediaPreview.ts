import { apiUrl } from '@/api/client'

/**
 * Kinship proxy preview URL for an attached Nextcloud file id.
 * Gated by genealogy view permission on the backend.
 * @param fileId
 * @param size
 */
export function mediaPreviewUrl(fileId: number, size = 256): string {
	return apiUrl(`/media/files/${fileId}/preview`)
		+ `?x=${size}&y=${size}&a=1`
}

/**
 * Kinship proxy download URL for an attached Nextcloud file id.
 * @param fileId
 */
export function mediaDownloadUrl(fileId: number): string {
	return apiUrl(`/media/files/${fileId}/download`)
}

/**
 * Fancybox gallery supports image and video sources only.
 * @param mime
 */
export function isLightboxMime(mime: string | null | undefined): boolean {
	if (!mime) {
		return false
	}
	return mime.startsWith('image/') || mime.startsWith('video/')
}
