import { generateUrl } from '@nextcloud/router'

/**
 * Nextcloud core preview URL for a file id.
 * @param fileId
 * @param size
 */
export function mediaPreviewUrl(fileId: number, size = 256): string {
	return generateUrl('/core/preview')
		+ `?fileId=${fileId}&x=${size}&y=${size}&a=1`
}
