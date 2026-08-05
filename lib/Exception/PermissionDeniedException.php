<?php

declare(strict_types=1);

namespace OCA\Kinship\Exception;

class PermissionDeniedException extends \RuntimeException {
	public function __construct(
		string $message = 'Permission denied',
		private ?string $action = null,
	) {
		parent::__construct($message);
	}

	public function getAction(): ?string {
		return $this->action;
	}
}
