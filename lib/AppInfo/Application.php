<?php

declare(strict_types=1);

namespace OCA\Kinship\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
	public const APP_ID = 'kinship';

	/** @psalm-suppress PossiblyUnusedMethod */
	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		require_once __DIR__ . '/../../vendor/autoload.php';

		// Controllers, services, mappers and importers are concrete classes
		// with type-hinted constructors, so Nextcloud's DI container autowires
		// them; no manual registration is needed. Register a service here only
		// when binding an interface to an implementation or when a class cannot
		// be autowired.
	}

	public function boot(IBootContext $context): void {
	}
}
