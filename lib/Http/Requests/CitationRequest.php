<?php

declare(strict_types=1);

namespace OCA\Kinship\Http\Requests;

use Closure;
use Illuminate\Validation\Rule;
use OCA\Kinship\Constants\CitationConfidence;
use OCA\Kinship\Constants\SubjectType;
use OCA\Kinship\Db\SourceMapper;
use OCP\Server;

class CitationRequest extends FormRequest {
	public function rules(): array {
		return [
			'sourceId' => [
				'required',
				'integer',
				'min:1',
				function (string $attribute, mixed $value, Closure $fail): void {
					if (Server::get(SourceMapper::class)->findOrNull((int)$value) === null) {
						$fail('Select a valid source');
					}
				},
			],
			'subjectType' => ['required', 'string', Rule::in(SubjectType::CITABLE)],
			'subjectId' => ['required', 'integer', 'min:1'],
			'detail' => ['nullable', 'string', 'max:255'],
			'transcription' => ['nullable', 'string'],
			'confidence' => ['required', 'string', Rule::in(CitationConfidence::ALL)],
			'accessedDate' => ['nullable', 'date_format:Y-m-d'],
		];
	}

	public function messages(): array {
		return [
			'sourceId.required' => 'Source is required',
			'sourceId.min' => 'Source is required',
			'subjectType.required' => 'Subject type is required',
			'subjectType.in' => 'Select a valid subject type',
			'subjectId.required' => 'Subject is required',
			'subjectId.min' => 'Subject is required',
			'detail.max' => 'Detail must be 255 characters or fewer',
			'confidence.in' => 'Select a valid confidence level',
			'accessedDate.date_format' => 'Use date format YYYY-MM-DD',
		];
	}

	public function attributes(): array {
		return [
			'sourceId' => 'source',
			'subjectType' => 'subject type',
			'subjectId' => 'subject',
			'accessedDate' => 'accessed date',
		];
	}
}
