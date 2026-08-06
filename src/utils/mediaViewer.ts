import { Fancybox } from '@fancyapps/ui'
import '@fancyapps/ui/dist/fancybox/fancybox.css'
import { showError } from '@nextcloud/dialogs'
import type { Media, MediaFileSummary } from '@/types'
import { isLightboxMime, mediaDownloadUrl, mediaPreviewUrl } from '@/utils/mediaPreview.ts'
import { generateUrl } from '@nextcloud/router'
import type { CarouselSlide } from '@fancyapps/ui/dist/carousel/carousel'

function buildSlide(media: Media): Partial<CarouselSlide> | null {
	const mime = media.file?.mime ?? ''
	const caption = media.caption || media.file?.name || undefined

	if (mime.startsWith('image/')) {
		return {
			src: mediaPreviewUrl(media.fileId, 2048),
			type: 'image',
			caption,
			thumbSrc: mediaPreviewUrl(media.fileId, 256),
		}
	}

	if (mime.startsWith('video/')) {
		// Fancybox only auto-detects HTML5 video from URL extensions
		// (.mp4/.webm/…). Our proxy URLs have none, so set type + format.
		return {
			src: mediaDownloadUrl(media.fileId),
			type: 'html5video',
			html5videoFormat: mime,
			thumbSrc: generateUrl('/core/mimeicon?mime=video'),
			caption,
		}
	}

	return null
}

/**
 * Fancybox supports image and video sources in this app.
 * @param mime
 */
export function canOpenInViewer(mime: string | null | undefined): boolean {
	return isLightboxMime(mime)
}

/**
 * Open image/video media in Fancybox. Pass siblings for slideshow navigation
 * (non-image/video siblings are skipped).
 * @param media
 * @param siblings
 */
export function openMediaInViewer(media: Media, siblings: Media[] = []): boolean {
	if (!media.fileId) {
		showError('File is unavailable')
		return false
	}

	if (!isLightboxMime(media.file?.mime)) {
		showError('Preview is only available for images and videos')
		return false
	}

	const candidates = (siblings.length > 0 ? siblings : [media])
		.filter((item) => isLightboxMime(item.file?.mime))

	const slides = candidates
		.map(buildSlide)
		.filter((slide): slide is Partial<CarouselSlide> => slide !== null)

	if (slides.length === 0) {
		showError('Preview is only available for images and videos')
		return false
	}

	const startIndex = Math.max(0, candidates.findIndex((item) => item.id === media.id))

	try {
		Fancybox.close()
		Fancybox.show(slides, {
			startIndex,
			// App uses vue-router hash history; Fancybox must not touch the URL hash.
			Hash: false,
			Carousel: {
				infinite: true,
				Thumbs: {
					type: 'classic',
					showOnStart: false,
				},
			},
		})
		return true
	} catch (e) {
		console.error('Failed to open Fancybox', e)
		showError('Could not open file preview')
		return false
	}
}

/**
 * Trigger a browser download via the Kinship media proxy.
 * @param file
 * @param fileId
 */
export function downloadMediaFile(
	file: MediaFileSummary | null | undefined,
	fileId?: number,
): void {
	const id = fileId ?? file?.fileId
	if (!id) {
		showError('File is unavailable for download')
		return
	}

	const link = document.createElement('a')
	link.href = mediaDownloadUrl(id)
	link.download = file?.name || `kinship-file-${id}`
	link.rel = 'noopener'
	document.body.appendChild(link)
	link.click()
	link.remove()
}
