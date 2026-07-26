<?php

declare(strict_types=1);

namespace OCA\Kinship\Constants;

/**
 * Lifecycle status and operation of a proposed change (kinship_claims).
 *
 * The full resolution workflow (confidence weighting, conflicting evidence)
 * arrives in v0.3; v0.1 only records the proposal and its pending/accepted/
 * rejected state.
 */
final class ClaimStatus {
	public const PENDING = 'pending';
	public const ACCEPTED = 'accepted';
	public const REJECTED = 'rejected';

	public const ALL = [
		self::PENDING,
		self::ACCEPTED,
		self::REJECTED,
	];

	public const OP_CREATE = 'create';
	public const OP_UPDATE = 'update';
	public const OP_DELETE = 'delete';

	public const OPERATIONS = [
		self::OP_CREATE,
		self::OP_UPDATE,
		self::OP_DELETE,
	];

	public static function isValidStatus(string $status): bool {
		return in_array($status, self::ALL, true);
	}

	public static function isValidOperation(string $operation): bool {
		return in_array($operation, self::OPERATIONS, true);
	}
}
