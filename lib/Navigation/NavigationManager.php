<?php

declare(strict_types=1);

namespace OCA\Kinship\Navigation;

use OCP\INavigationEntryProvider;

class NavigationManager implements INavigationEntryProvider {
	public function getNavigationEntries(): array {
		return [
			[
				'id' => 'kinship',
				'order' => 50,
				'href' => '/index.php/apps/kinship/',
				'icon' => '/apps/kinship/img/app.svg',
				'dark-icon' => '/apps/kinship/img/app-dark.svg',
				'name' => 'Kinship',
			],
		];
	}
}
