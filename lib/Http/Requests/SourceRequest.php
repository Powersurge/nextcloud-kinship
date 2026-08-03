<?php

declare(strict_types=1);

namespace OCA\Kinship\Http\Requests;

use Illuminate\Validation\Rule;
use OCA\Kinship\Constants\SourceType;

class SourceRequest extends FormRequest {
	public function rules(): array {
		return [
			'title' => ['required', 'string', 'max:255'],
			'sourceType' => ['required', 'string', Rule::in(SourceType::ALL)],
			'author' => ['nullable', 'string', 'max:255'],
			'publication' => ['nullable', 'string', 'max:255'],
			'repository' => ['nullable', 'string', 'max:255'],
			'url' => ['nullable', 'url', 'max:512'],
			'notes' => ['nullable', 'string'],
		];
	}

	public function messages(): array {
		return [
			'title.required' => 'Title is required',
			'sourceType.in' => 'Select a valid source type',
			'title.max' => 'Title must be 255 characters or fewer',
			'author.max' => 'Author must be 255 characters or fewer',
			'publication.max' => 'Publication must be 255 characters or fewer',
			'repository.max' => 'Repository must be 255 characters or fewer',
			'url.url' => 'Enter a valid URL',
			'url.max' => 'URL must be 512 characters or fewer',
		];
	}

	public function attributes(): array {
		return [
			'sourceType' => 'source type',
		];
	}
}
