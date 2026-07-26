<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * Derived family membership row (role is 'parent' or 'child').
 * Auto-maintained from relationships; never edited directly.
 *
 * @method int getFamilyId()
 * @method void setFamilyId(int $familyId)
 * @method int getPersonId()
 * @method void setPersonId(int $personId)
 * @method string getRole()
 * @method void setRole(string $role)
 * @method ?int getCreatedAt()
 * @method void setCreatedAt(?int $createdAt)
 */
class FamilyMember extends Entity implements JsonSerializable {
	public const ROLE_PARENT = 'parent';
	public const ROLE_CHILD = 'child';

	protected int $familyId = 0;
	protected int $personId = 0;
	protected string $role = self::ROLE_CHILD;
	protected ?int $createdAt = null;

	public function __construct() {
		$this->addType('familyId', 'integer');
		$this->addType('personId', 'integer');
		$this->addType('createdAt', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'familyId' => $this->familyId,
			'personId' => $this->personId,
			'role' => $this->role,
			'createdAt' => $this->createdAt,
		];
	}
}
