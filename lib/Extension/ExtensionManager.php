<?php

declare(strict_types=1);

namespace OCA\Kinship\Extension;

class ExtensionManager {
	public function __construct(
		private ExtensionRegistry $registry,
	) {
	}

	public function register(
		IKinshipExtension $extension,
	): void {
		$this->registry->register(
			$extension
		);
	}

	/**
	 * @return IKinshipExtension[]
	 */
	public function getInstalled(): array {
		return $this->registry->getExtensions();
	}
}
