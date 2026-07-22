<?php

declare(strict_types=1);

use OCP\Util;

Util::addScript(OCA\Kinship\AppInfo\Application::APP_ID, OCA\Kinship\AppInfo\Application::APP_ID . '-main');
Util::addStyle(OCA\Kinship\AppInfo\Application::APP_ID, OCA\Kinship\AppInfo\Application::APP_ID . '-main');

?>

<div id="kinship"></div>
