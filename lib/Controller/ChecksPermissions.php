<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Exception\PermissionDeniedException;
use OCA\Kinship\Service\PermissionService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

/**
 * Shared deny-unless-authorized helper for API controllers.
 */
trait ChecksPermissions {
	protected function denyUnlessCan(PermissionService $permissions, string $action): ?DataResponse {
		try {
			$permissions->requireCan($action);

			return null;
		} catch (PermissionDeniedException $e) {
			return new DataResponse(
				['error' => $e->getMessage()],
				Http::STATUS_FORBIDDEN,
			);
		}
	}
}
