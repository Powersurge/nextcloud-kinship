<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Kinship\Listener;

use OCP\AppFramework\Http\EmptyContentSecurityPolicy;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

/**
 * @template-implements IEventListener<Event>
 */
final class LaxifyCSP implements IEventListener {
	#[\Override]
	public function handle(Event $event): void {
		if (!($event instanceof AddContentSecurityPolicyEvent)) {
			return;
		}

		// Unblock HMR requests.
		$csp = new EmptyContentSecurityPolicy();
        $csp->addAllowedFrameDomain('*');
        $csp->addAllowedConnectDomain('*');
        $csp->addAllowedScriptDomain('https://*.tile.openstreetmap.org');

		$event->addPolicy($csp);
	}
}
