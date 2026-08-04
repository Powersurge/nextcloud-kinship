import { showConfirmation } from '@nextcloud/dialogs'

export const showDeleteConfirmationDialog = ({ name, text }: {name?: string, text?: string} = {}) => {
	return showConfirmation({
		name: name || 'Are you sure?',
		text: text || 'This action cannot be undone.',
		labelReject: 'Cancel',
		severity: 'error',
	})
}
