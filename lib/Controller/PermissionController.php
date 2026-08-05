<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Service\PermissionService;
use OCA\Kinship\Settings\KinshipAdminSettings;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IUserManager;

class PermissionController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private PermissionService $service,
		private IUserManager $userManager,
	) {
		parent::__construct($appName, $request);
	}

	#[AuthorizedAdminSetting(settings: KinshipAdminSettings::class)]
	public function index(): DataResponse {
		$permissions = [];
		foreach ($this->service->getAll() as $permission) {
			$row = $permission->jsonSerialize();
			$user = $this->userManager->get($permission->getUid());
			$row['displayName'] = $user?->getDisplayName() ?? $permission->getUid();
			$permissions[] = $row;
		}

		return new DataResponse(['permissions' => $permissions]);
	}

	/**
	 * The current user's effective role and capabilities.
	 */
	#[NoAdminRequired]
	public function me(): DataResponse {
		$uid = $this->service->currentUid();
		if ($uid === null) {
			return new DataResponse([
				'uid' => null,
				'role' => null,
				'capabilities' => [],
			]);
		}

		return new DataResponse([
			'uid' => $uid,
			'role' => $this->service->getEffectiveRole($uid),
			'capabilities' => $this->service->capabilities($uid),
		]);
	}

	/**
	 * Grant (or update) a role for a user.
	 *
	 * @param array{uid?: string, role?: string} $data
	 */
	#[AuthorizedAdminSetting(settings: KinshipAdminSettings::class)]
	public function create(array $data): DataResponse {
		$uid = trim((string)($data['uid'] ?? ''));
		$role = (string)($data['role'] ?? PermissionRole::VIEWER);

		if ($uid === '') {
			return new DataResponse(
				['error' => 'User id is required'],
				Http::STATUS_BAD_REQUEST,
			);
		}

		if ($this->userManager->get($uid) === null) {
			return new DataResponse(
				['error' => 'User not found'],
				Http::STATUS_BAD_REQUEST,
			);
		}

		if (!PermissionRole::isValid($role)) {
			return new DataResponse(
				['error' => 'Unknown permission role'],
				Http::STATUS_BAD_REQUEST,
			);
		}

		try {
			$permission = $this->service->grant($uid, $role);
		} catch (\InvalidArgumentException $e) {
			return new DataResponse(
				['error' => $e->getMessage()],
				Http::STATUS_BAD_REQUEST,
			);
		}

		$row = $permission->jsonSerialize();
		$user = $this->userManager->get($permission->getUid());
		$row['displayName'] = $user?->getDisplayName() ?? $permission->getUid();

		return new DataResponse($row, Http::STATUS_CREATED);
	}

	#[AuthorizedAdminSetting(settings: KinshipAdminSettings::class)]
	public function delete(int $id): DataResponse {
		try {
			$this->service->revoke($id);
		} catch (\Throwable $e) {
			return new DataResponse(
				['error' => 'Permission grant not found'],
				Http::STATUS_NOT_FOUND,
			);
		}

		return new DataResponse(['deleted' => true]);
	}

	/**
	 * Search Nextcloud users for the admin role-grant UI.
	 */
	#[AuthorizedAdminSetting(settings: KinshipAdminSettings::class)]
	public function searchUsers(string $search = ''): DataResponse {
		$search = trim($search);
		$users = [];

		foreach ($this->userManager->search($search, 25) as $user) {
			$users[] = [
				'uid' => $user->getUID(),
				'displayName' => $user->getDisplayName(),
			];
		}

		return new DataResponse(['users' => $users]);
	}
}
