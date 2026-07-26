<?php

declare(strict_types=1);

namespace OCA\Kinship\Constants;

/**
 * Core permission roles (kinship_permissions.role).
 *
 * v0.1 only records ownership and role grants; enforcement and object-level
 * scoping land with sharing in v0.2. Ordered most to least privileged.
 */
final class PermissionRole {
	public const OWNER = 'owner';
	public const EDITOR = 'editor';
	public const CONTRIBUTOR = 'contributor';
	public const VIEWER = 'viewer';

	public const ALL = [
		self::OWNER,
		self::EDITOR,
		self::CONTRIBUTOR,
		self::VIEWER,
	];

	/** Higher number = more privilege. */
	public const RANK = [
		self::VIEWER => 1,
		self::CONTRIBUTOR => 2,
		self::EDITOR => 3,
		self::OWNER => 4,
	];

	public const SCOPE_GLOBAL = 'global';

	public static function isValid(string $role): bool {
		return in_array($role, self::ALL, true);
	}

	public static function rankOf(string $role): int {
		return self::RANK[$role] ?? 0;
	}
}
