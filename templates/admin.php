<?php

declare(strict_types=1);

$vite = 'http://localhost:5173';

if (@file_get_contents($vite . '/@vite/client')) {
	$nonce = $_['cspNonce'];

	echo '<script nonce="' . $nonce . '" type="module" src="' . $vite . '/@vite/client"></script>';
	echo '<script nonce="' . $nonce . '" type="module" src="' . $vite . '/src/admin.ts"></script>';
}

?>

<div id="kinship-admin"></div>
