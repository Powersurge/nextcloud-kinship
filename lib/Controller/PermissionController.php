<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Service\PermissionService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class PermissionController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private PermissionService $service,
	) {
		parent::__construct($appName, $request);
	}

	public function index(): DataResponse {
		return new DataResponse(['permissions' => $this->service->getAll()]);
	}

	/**
	 * The current user's global role.
	 */
	public function me(): DataResponse {
		$uid = $this->service->currentUid();

		return new DataResponse([
			'uid' => $uid,
			'role' => $uid !== null ? $this->service->getRole($uid) : null,
		]);
	}

	/**
	 * Grant (or update) a role for a user.
	 */
	public function create(array $data): DataResponse {
		$permission = $this->service->grant(
			$data['uid'] ?? '',
			$data['role'] ?? PermissionRole::VIEWER,
		);

		return new DataResponse($permission, Http::STATUS_CREATED);
	}

	public function delete(int $id): DataResponse {
		$this->service->revoke($id);

		return new DataResponse(['deleted' => true]);
	}
}
