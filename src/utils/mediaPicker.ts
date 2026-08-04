import { getFilePickerBuilder, FilePickerClosed } from '@nextcloud/dialogs'
import '@nextcloud/dialogs/style.css'

/**
 * Open the Nextcloud file picker and return selected file ids.
 * Returns null if the user cancels.
 */
export async function pickMediaFileIds(options?: {
	title?: string
	multiSelect?: boolean
}): Promise<number[] | null> {
	const multi = options?.multiSelect !== false

	try {
		const picker = getFilePickerBuilder(options?.title ?? 'Select media')
			.setMultiSelect(multi)
			.addMimeTypeFilter('image/*')
			.addMimeTypeFilter('video/*')
			.addMimeTypeFilter('application/pdf')
			.addButton({
				label: 'Select',
				variant: 'primary',
				callback: () => undefined,
			})
			.build()

		const nodes = await picker.pickNodes()
		const ids = nodes
			.map((node) => node.fileid)
			.filter((id): id is number => typeof id === 'number' && id > 0)

		return ids.length ? ids : null
	} catch (e) {
		if (e instanceof FilePickerClosed) {
			return null
		}
		throw e
	}
}

/**
 * Open a hidden file input for local uploads. Returns null if cancelled.
 */
export function pickLocalFiles(options?: {
	accept?: string
	multiple?: boolean
}): Promise<File[] | null> {
	return new Promise((resolve) => {
		const input = document.createElement('input')
		input.type = 'file'
		input.accept = options?.accept ?? 'image/*,video/*,application/pdf'
		input.multiple = options?.multiple !== false
		input.style.display = 'none'
		document.body.appendChild(input)

		const cleanup = () => {
			input.remove()
		}

		input.addEventListener('change', () => {
			const files = input.files ? Array.from(input.files) : []
			cleanup()
			resolve(files.length ? files : null)
		})

		// If the dialog is cancelled, change may never fire; listen once for focus return.
		window.addEventListener(
			'focus',
			() => {
				window.setTimeout(() => {
					if (document.body.contains(input) && !input.files?.length) {
						cleanup()
						resolve(null)
					}
				}, 300)
			},
			{ once: true },
		)

		input.click()
	})
}
