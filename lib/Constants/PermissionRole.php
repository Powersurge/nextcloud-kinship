<?php

declare(strict_types=1);

namespace OCA\Kinship\Constants;

/**
 * Permission roles (kinship_permissions.role).
 *
 * Ordered most to least privileged. Global grants are managed from NC Admin
 * Settings; object-level scoping lands with sharing.
 */
final class PermissionRole {
	public const OWNER = 'owner';
	public const EDITOR = 'editor';
	public const CONTRIBUTOR = 'contributor';
	public const VIEWER = 'viewer';
	public const NONE = 'none';

	public const ALL = [
		self::OWNER,
		self::EDITOR,
		self::CONTRIBUTOR,
		self::VIEWER,
	];

	/** Roles allowed as the instance default (owner must be granted explicitly). */
	public const DEFAULT_ROLE_OPTIONS = [
		self::NONE,
		self::VIEWER,
		self::CONTRIBUTOR,
		self::EDITOR,
	];

	/** Higher number = more privilege. */
	public const RANK = [
		self::VIEWER => 1,
		self::CONTRIBUTOR => 2,
		self::EDITOR => 3,
		self::OWNER => 4,
	];

	public const SCOPE_GLOBAL = 'global';

	public const ACTION_VIEW = 'view';
	public const ACTION_CREATE_PEOPLE = 'create_people';
	public const ACTION_EDIT = 'edit';
	public const ACTION_ADD_SOURCES = 'add_sources';
	public const ACTION_ADD_NOTES = 'add_notes';
	public const ACTION_ADD_CLAIMS = 'add_claims';
	public const ACTION_MANAGE_PARTICIPANTS = 'manage_participants';
	public const ACTION_ACCEPT_CLAIMS = 'accept_claims';
	public const ACTION_MERGE = 'merge';
	public const ACTION_DELETE = 'delete';
	public const ACTION_MANAGE_CUSTOM_EVENTS = 'manage_custom_events';
	public const ACTION_MANAGE_PLACES = 'manage_places';
	public const ACTION_MANAGE_PERMISSIONS = 'manage_permissions';

	public const ACTIONS = [
		self::ACTION_VIEW,
		self::ACTION_CREATE_PEOPLE,
		self::ACTION_EDIT,
		self::ACTION_ADD_SOURCES,
		self::ACTION_ADD_NOTES,
		self::ACTION_ADD_CLAIMS,
		self::ACTION_MANAGE_PARTICIPANTS,
		self::ACTION_ACCEPT_CLAIMS,
		self::ACTION_MERGE,
		self::ACTION_DELETE,
		self::ACTION_MANAGE_CUSTOM_EVENTS,
		self::ACTION_MANAGE_PLACES,
		self::ACTION_MANAGE_PERMISSIONS,
	];

	public static function isValid(string $role): bool {
		return in_array($role, self::ALL, true);
	}

	public static function rankOf(string $role): int {
		return self::RANK[$role] ?? 0;
	}
}
