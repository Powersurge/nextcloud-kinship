<?php

declare(strict_types=1);

namespace OCA\Kinship\Http\Requests;

/**
 * Laravel-style form request: rules, messages, and attributes for a payload.
 */
abstract class FormRequest {
	/**
	 * @return array<string, list<mixed>|string>
	 */
	abstract public function rules(): array;

	/**
	 * @return array<string, string>
	 */
	public function messages(): array {
		return [];
	}

	/**
	 * @return array<string, string>
	 */
	public function attributes(): array {
		return [];
	}

	/**
	 * Normalize input before validation (shared for all requests).
	 *
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	public function prepareForValidation(array $data): array {
		foreach ($data as $key => $value) {
			if (!is_string($value)) {
				continue;
			}

			$value = trim($value);
			$data[$key] = $value === '' ? null : $value;
		}

		return $data;
	}
}
