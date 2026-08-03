<?php

declare(strict_types=1);

namespace OCA\Kinship\Validation;

use OCA\Kinship\Exception\ValidationException;
use OCA\Kinship\Http\Requests\FormRequest;

/**
 * Runs Illuminate validation against a {@see FormRequest} definition.
 */
class RequestValidator {
	public function __construct(
		private ValidatorFactory $validatorFactory,
	) {
	}

	/**
	 * @param class-string<FormRequest> $formRequestClass
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 * @throws ValidationException
	 */
	public function validate(array $data, string $formRequestClass, bool $partial = false): array {
		if (!is_a($formRequestClass, FormRequest::class, true)) {
			throw new \InvalidArgumentException($formRequestClass . ' must extend ' . FormRequest::class);
		}

		/** @var FormRequest $formRequest */
		$formRequest = new $formRequestClass();
		$presentKeys = array_keys($data);
		$prepared = $formRequest->prepareForValidation($data);

		$rules = $formRequest->rules();
		if ($partial) {
			$rules = array_intersect_key($rules, array_flip($presentKeys));
		}

		$validator = $this->validatorFactory->make(
			$prepared,
			$rules,
			$formRequest->messages(),
			$formRequest->attributes(),
		);

		if ($validator->fails()) {
			throw new ValidationException($validator->errors()->toArray());
		}

		$validated = $this->normalizeValidated($validator->validated());

		if ($partial) {
			return $validated;
		}

		// Ensure every declared rule key is present for create-style payloads.
		$result = [];
		foreach (array_keys($formRequest->rules()) as $key) {
			$result[$key] = $validated[$key] ?? null;
		}

		return $result;
	}

	/**
	 * @param array<string, mixed> $validated
	 * @return array<string, mixed>
	 */
	private function normalizeValidated(array $validated): array {
		foreach ($validated as $key => $value) {
			if ($value === '') {
				$validated[$key] = null;
			}
		}

		return $validated;
	}
}
