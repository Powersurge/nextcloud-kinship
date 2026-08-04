<?php

declare(strict_types=1);

namespace OCA\Kinship\Http\Requests;

use Illuminate\Validation\Rule;
use OCA\Kinship\Constants\SubjectType;

class AlbumRequest extends FormRequest {
	public function rules(): array {
		return [
			'title' => ['required', 'string', 'max:255'],
			'description' => ['nullable', 'string'],
			'ownerType' => ['required', 'string', Rule::in(SubjectType::ALBUM_OWNERS)],
			'ownerId' => ['required', 'integer', 'min:1'],
			'coverMediaId' => ['nullable', 'integer', 'min:1'],
		];
	}

	public function messages(): array {
		return [
			'title.required' => 'Title is required',
			'title.max' => 'Title must be 255 characters or fewer',
			'ownerType.required' => 'Owner type is required',
			'ownerType.in' => 'Albums can only belong to a person, family, or event',
			'ownerId.required' => 'Owner is required',
			'ownerId.min' => 'Owner is required',
		];
	}

	public function attributes(): array {
		return [
			'ownerType' => 'owner type',
			'ownerId' => 'owner',
			'coverMediaId' => 'cover media',
		];
	}
}
