<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Db\Permission;
use OCA\Kinship\Db\PermissionMapper;
use OCA\Kinship\Exception\PermissionDeniedException;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IGroupManager;
use OCP\IUserSession;

/**
 * Global role grants and capability checks.
 *
 * Nextcloud admins are implicit Kinship owners. Users without an explicit
 * grant receive the admin-configured default role.
 */
class PermissionService {
	/**
	 * Minimum role rank required per action. Mirrors the v0.2 permissions
	 * matrix in docs/ROADMAP.md.
	 */
	private const ACTION_MIN_RANK = [
		PermissionRole::ACTION_VIEW => 1,
		PermissionRole::ACTION_CREATE_PEOPLE => 2,
		PermissionRole::ACTION_ADD_SOURCES => 2,
		PermissionRole::ACTION_ADD_NOTES => 2,
		PermissionRole::ACTION_ADD_CLAIMS => 2,
		PermissionRole::ACTION_MANAGE_PARTICIPANTS => 2,
		PermissionRole::ACTION_EDIT => 2,
		PermissionRole::ACTION_ACCEPT_CLAIMS => 3,
		PermissionRole::ACTION_MERGE => 3,
		PermissionRole::ACTION_DELETE => 3,
		PermissionRole::ACTION_MANAGE_CUSTOM_EVENTS => 3,
		PermissionRole::ACTION_MANAGE_PLACES => 3,
		PermissionRole::ACTION_MANAGE_PERMISSIONS => 4,
	];

	public function __construct(
		private PermissionMapper $mapper,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
		private AdminSettingsService $adminSettings,
		private IGroupManager $groupManager,
	) {
	}

	public function currentUid(): ?string {
		return $this->userSession->getUser()?->getUID();
	}

	/**
	 * @return Permission[]
	 */
	public function getAll(): array {
		return $this->mapper->findAll();
	}

	/**
	 * The stored role grant at a scope, or null if none.
	 */
	public function getRole(
		string $uid,
		string $scopeType = PermissionRole::SCOPE_GLOBAL,
		?int $scopeId = null,
	): ?string {
		return $this->mapper->findRole($uid, $scopeType, $scopeId)?->getRole();
	}

	/**
	 * Effective role used for capability checks.
	 */
	public function getEffectiveRole(string $uid): ?string {
		if ($this->groupManager->isAdmin($uid)) {
			return PermissionRole::OWNER;
		}

		$granted = $this->getRole($uid);
		if ($granted !== null) {
			return $granted;
		}

		$default = $this->adminSettings->getDefaultRole();
		if ($default === PermissionRole::NONE) {
			return null;
		}

		return $default;
	}

	public function can(string $uid, string $action): bool {
		$role = $this->getEffectiveRole($uid);
		if ($role === null) {
			return false;
		}

		$required = self::ACTION_MIN_RANK[$action] ?? PHP_INT_MAX;
		if (PermissionRole::rankOf($role) < $required) {
			return false;
		}

		if ($role === PermissionRole::EDITOR) {
			if ($action === PermissionRole::ACTION_DELETE && !$this->adminSettings->isEditorCanDelete()) {
				return false;
			}
			if ($action === PermissionRole::ACTION_ACCEPT_CLAIMS && !$this->adminSettings->isEditorCanAcceptClaims()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @throws PermissionDeniedException
	 */
	public function requireCan(string $action): void {
		$uid = $this->currentUid();
		if ($uid === null || !$this->can($uid, $action)) {
			throw new PermissionDeniedException(
				'You do not have permission to perform this action.',
				$action,
			);
		}
	}

	/**
	 * @return array<string, bool>
	 */
	public function capabilities(string $uid): array {
		$capabilities = [];
		foreach (PermissionRole::ACTIONS as $action) {
			$capabilities[$action] = $this->can($uid, $action);
		}

		return $capabilities;
	}

	/**
	 * Grant (or update) a role for a user at the global scope.
	 */
	public function grant(
		string $uid,
		string $role,
		string $scopeType = PermissionRole::SCOPE_GLOBAL,
		?int $scopeId = null,
	) {
		if (!PermissionRole::isValid($role)) {
			throw new \InvalidArgumentException('Unknown permission role: ' . $role);
		}

		$now = $this->timeFactory->getTime();
		$existing = $this->mapper->findRole($uid, $scopeType, $scopeId);

		if ($existing !== null) {
			$existing->setRole($role);
			$existing->setGrantedBy($this->currentUid());
			$existing->setUpdatedAt($now);

			return $this->mapper->update($existing);
		}

		$permission = new Permission();
		$permission->setUid($uid);
		$permission->setRole($role);
		$permission->setScopeType($scopeType);
		$permission->setScopeId($scopeId);
		$permission->setGrantedBy($this->currentUid());
		$permission->setCreatedAt($now);
		$permission->setUpdatedAt($now);

		return $this->mapper->insert($permission);
	}

	public function revoke(int $id): void {
		$this->mapper->delete($this->mapper->find($id));
	}
}
