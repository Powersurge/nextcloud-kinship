<?php

declare(strict_types=1);

namespace OCA\Kinship\Exception;

/**
 * Carries field-level validation errors for API clients.
 *
 * Shape: ['fieldName' => ['message', ...], ...]
 */
class ValidationException extends \InvalidArgumentException {
	/**
	 * @param array<string, list<string>> $errors
	 */
	public function __construct(
		private array $errors,
		string $message = 'Validation failed',
	) {
		parent::__construct($message);
	}

	/**
	 * @return array<string, list<string>>
	 */
	public function getErrors(): array {
		return $this->errors;
	}
}
