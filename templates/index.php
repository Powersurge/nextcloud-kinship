<?php

declare(strict_types=1);

use OCP\Util;

$vite = 'http://localhost:5173';

if (@file_get_contents($vite . '/@vite/client')) {
	$nonce = $_['cspNonce'];

	echo '<script nonce="' . $nonce . '" type="module" src="' . $vite . '/@vite/client"></script>';
	echo '<script nonce="' . $nonce . '" type="module" src="' . $vite . '/src/main.js"></script>';
} else {
	Util::addScript(OCA\Kinship\AppInfo\Application::APP_ID, OCA\Kinship\AppInfo\Application::APP_ID . '-main');
	Util::addStyle(OCA\Kinship\AppInfo\Application::APP_ID, OCA\Kinship\AppInfo\Application::APP_ID . '-main');
}

?>

<div id="kinship"></div>
