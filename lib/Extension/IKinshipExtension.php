<?php

declare(strict_types=1);

namespace OCA\Kinship\Extension;

interface IKinshipExtension {
	/**
	 * Unique extension identifier.
	 */
	public function getId(): string;

	/**
	 * Display name.
	 */
	public function getName(): string;

	/**
	 * Extension version.
	 */
	public function getVersion(): string;

	/**
	 * Called when extension loads.
	 */
	public function register(): void;
}
