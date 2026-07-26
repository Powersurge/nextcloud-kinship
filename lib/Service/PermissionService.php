<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\PermissionRole;
use OCA\Kinship\Db\Permission;
use OCA\Kinship\Db\PermissionMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

/**
 * Core permissions model (v0.1 scaffolding).
 *
 * Records role grants and answers role/capability questions. Actual
 * enforcement across controllers and object-level scoping land with sharing in
 * v0.2; this service intentionally does not block anything yet.
 */
class PermissionService {
	/**
	 * Minimum role rank required per action. Mirrors the v0.2 permissions
	 * matrix in docs/ROADMAP.md.
	 */
	private const ACTION_MIN_RANK = [
		'view' => 1,           // viewer
		'create_people' => 2,  // contributor
		'add_sources' => 2,
		'add_notes' => 2,
		'add_claims' => 2,
		'manage_participants' => 2,
		'edit' => 2,
		'accept_claims' => 3,  // editor
		'merge' => 3,
		'delete' => 3,
		'manage_custom_events' => 3,
		'manage_places' => 3,
		'manage_permissions' => 4, // owner
	];

	public function __construct(
		private PermissionMapper $mapper,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
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
	 * The role a user holds at a scope, or null if none granted.
	 */
	public function getRole(
		string $uid,
		string $scopeType = PermissionRole::SCOPE_GLOBAL,
		?int $scopeId = null,
	): ?string {
		return $this->mapper->findRole($uid, $scopeType, $scopeId)?->getRole();
	}

	/**
	 * Whether a user may perform an action (v0.1: advisory only).
	 */
	public function can(string $uid, string $action): bool {
		$role = $this->getRole($uid);
		if ($role === null) {
			return false;
		}

		$required = self::ACTION_MIN_RANK[$action] ?? PHP_INT_MAX;

		return PermissionRole::rankOf($role) >= $required;
	}

	/**
	 * Grant (or update) a role for a user at the global scope.
	 */
	public function grant(
		string $uid,
		string $role,
		string $scopeType = PermissionRole::SCOPE_GLOBAL,
		?int $scopeId = null,
	): Permission {
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
