import { generateRemoteUrl } from '@nextcloud/router'
import { getCurrentUser } from '@nextcloud/auth'
import { showError } from '@nextcloud/dialogs'
import type { Media, MediaFileSummary } from '@/types'

export type ViewerFileInfo = {
	filename: string
	basename: string
	mime: string
	fileid: number
	hasPreview: boolean
	size?: number
	mtime?: number
}

type OcaViewer = {
	open: (options: {
		path?: string
		fileInfo?: ViewerFileInfo
		list?: ViewerFileInfo[]
		enableSidebar?: boolean
	}) => void
	mimetypes?: string[]
}

declare global {
	interface Window {
		OCA?: {
			Viewer?: OcaViewer
		}
	}
}

function encodeDavPath(path: string): string {
	return path
		.split('/')
		.map((segment) => (segment === '' ? '' : encodeURIComponent(segment)))
		.join('/')
}

export function toViewerFileInfo(media: Media): ViewerFileInfo | null {
	const file = media.file
	if (!file?.path || !file.name || !file.mime) {
		return null
	}

	const mime = file.mime
	return {
		filename: file.path.startsWith('/') ? file.path : `/${file.path}`,
		basename: file.name,
		mime,
		fileid: media.fileId,
		hasPreview: mime.startsWith('image/') || mime.startsWith('video/'),
		size: file.size,
		mtime: file.mtime,
	}
}

function getViewer(): OcaViewer | null {
	return window.OCA?.Viewer ?? null
}

/**
 * Whether the Nextcloud Viewer (or a registered handler like Text/Office)
 * can open this mime type.
 * @param mime
 */
export function canOpenInViewer(mime: string | null | undefined): boolean {
	if (!mime) {
		return false
	}
	const viewer = getViewer()
	if (!viewer) {
		return false
	}
	const mimes = viewer.mimetypes ?? []
	if (mimes.length === 0) {
		// Viewer present but handlers not enumerated yet — allow common types.
		return mime.startsWith('image/')
			|| mime.startsWith('video/')
			|| mime.startsWith('audio/')
			|| mime === 'application/pdf'
			|| mime.startsWith('text/')
	}
	return mimes.includes(mime)
		|| mimes.includes(mime.split('/')[0] + '/*')
}

/**
 * Open a media file in the Nextcloud Viewer slideshow / preview.
 * Pass siblings so image/video navigation works like Files.
 * @param media
 * @param siblings
 */
export function openMediaInViewer(media: Media, siblings: Media[] = []): boolean {
	const viewer = getViewer()
	const fileInfo = toViewerFileInfo(media)
	if (!viewer || !fileInfo) {
		showError('File preview is unavailable. Is the Viewer app enabled?')
		return false
	}

	const list = siblings
		.map(toViewerFileInfo)
		.filter((item): item is ViewerFileInfo => item !== null)

	try {
		viewer.open({
			fileInfo,
			list: list.length > 0 ? list : [fileInfo],
		})
		return true
	} catch (e) {
		console.error('Failed to open viewer', e)
		showError('Could not open file preview')
		return false
	}
}

/**
 * Trigger a browser download of the Nextcloud file via WebDAV.
 * @param file
 */
export function downloadMediaFile(file: MediaFileSummary | null | undefined): void {
	if (!file?.path || !file.name) {
		showError('File is unavailable for download')
		return
	}

	const user = getCurrentUser()
	if (!user?.uid) {
		showError('You must be logged in to download files')
		return
	}

	const path = file.path.startsWith('/') ? file.path : `/${file.path}`
	const url = generateRemoteUrl(`dav/files/${user.uid}${encodeDavPath(path)}`)

	const link = document.createElement('a')
	link.href = url
	link.download = file.name
	link.rel = 'noopener'
	document.body.appendChild(link)
	link.click()
	link.remove()
}
