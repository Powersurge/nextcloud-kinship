<?php

declare(strict_types=1);

namespace OCA\Kinship\Http\Requests;

use Closure;
use Illuminate\Validation\Rule;
use OCA\Kinship\Constants\SubjectType;
use OCA\Kinship\Db\AlbumMapper;
use OCP\Server;

class MediaRequest extends FormRequest {
	public function rules(): array {
		return [
			'fileId' => ['required', 'integer', 'min:1'],
			'subjectType' => ['required', 'string', Rule::in(SubjectType::ALL)],
			'subjectId' => ['required', 'integer', 'min:1'],
			'albumId' => [
				'nullable',
				'integer',
				'min:1',
				function (string $attribute, mixed $value, Closure $fail): void {
					if ($value === null) {
						return;
					}
					$album = Server::get(AlbumMapper::class)->findOrNull((int)$value);
					if ($album === null) {
						$fail('Select a valid album');
					}
				},
			],
			'caption' => ['nullable', 'string', 'max:255'],
			'isPrimary' => ['nullable', 'boolean'],
			'sortOrder' => ['nullable', 'integer'],
		];
	}

	public function messages(): array {
		return [
			'fileId.required' => 'File is required',
			'fileId.min' => 'File is required',
			'subjectType.required' => 'Subject type is required',
			'subjectType.in' => 'Select a valid subject type',
			'subjectId.required' => 'Subject is required',
			'subjectId.min' => 'Subject is required',
			'caption.max' => 'Caption must be 255 characters or fewer',
		];
	}

	public function attributes(): array {
		return [
			'fileId' => 'file',
			'subjectType' => 'subject type',
			'subjectId' => 'subject',
			'albumId' => 'album',
			'isPrimary' => 'primary',
			'sortOrder' => 'sort order',
		];
	}
}
