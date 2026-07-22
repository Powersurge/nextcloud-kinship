<?php

declare(strict_types=1);

namespace OCA\Kinship\Extension;

class ExtensionRegistry {
	private array $extensions = [];

	public function register(
		IKinshipExtension $extension,
	): void {
		$this->extensions[
			$extension->getId()
		] = $extension;

		$extension->register();
	}

	/**
	 * @return IKinshipExtension[]
	 */
	public function getExtensions(): array {
		return array_values(
			$this->extensions
		);
	}

	public function get(
		string $id,
	): ?IKinshipExtension {
		return $this->extensions[$id] ?? null;
	}
}
