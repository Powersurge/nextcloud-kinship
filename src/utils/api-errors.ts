export type FieldErrors = Record<string, string[]>

type ApiErrorBody = {
	error?: unknown
	errors?: unknown
}

type ApiError = {
	response?: {
		data?: ApiErrorBody
	}
}

function isApiError(error: unknown): error is ApiError {
	return typeof error === 'object'
		&& error !== null
		&& 'response' in error
}

/**
 * Extract field-level validation errors from an API failure response.
 * Expected shape: { errors: { field: string[] } }
 * @param error
 */
export function getFieldErrors(error: unknown): FieldErrors | null {
	if (!isApiError(error)) {
		return null
	}

	const errors = error.response?.data?.errors
	if (!errors || typeof errors !== 'object' || Array.isArray(errors)) {
		return null
	}

	const fieldErrors: FieldErrors = {}
	for (const [field, messages] of Object.entries(errors)) {
		if (!Array.isArray(messages)) {
			continue
		}
		const list = messages.filter((message): message is string => typeof message === 'string')
		if (list.length > 0) {
			fieldErrors[field] = list
		}
	}

	return Object.keys(fieldErrors).length > 0 ? fieldErrors : null
}

export function getErrorMessage(error: unknown, fallback = 'Something went wrong.'): string {
	if (isApiError(error)) {
		const message = error.response?.data?.error
		if (typeof message === 'string' && message.trim() !== '') {
			return message
		}
	}
	return fallback
}
