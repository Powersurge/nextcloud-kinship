<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * A role grant. scope_type is 'global' in v0.1; object-level scoping and
 * enforcement land in v0.2.
 *
 * @method string getUid()
 * @method void setUid(string $uid)
 * @method string getRole()
 * @method void setRole(string $role)
 * @method string getScopeType()
 * @method void setScopeType(string $scopeType)
 * @method ?int getScopeId()
 * @method void setScopeId(?int $scopeId)
 * @method ?string getGrantedBy()
 * @method void setGrantedBy(?string $grantedBy)
 * @method ?int getCreatedAt()
 * @method void setCreatedAt(?int $createdAt)
 * @method ?int getUpdatedAt()
 * @method void setUpdatedAt(?int $updatedAt)
 */
class Permission extends Entity implements JsonSerializable {
	protected string $uid = '';
	/** Empty default so setters always dirty-mark for insert (Entity skips unchanged values). */
	protected string $role = '';
	/** Empty default so setters always dirty-mark for insert. */
	protected string $scopeType = '';
	protected ?int $scopeId = null;
	protected ?string $grantedBy = null;
	protected ?int $createdAt = null;
	protected ?int $updatedAt = null;

	public function __construct() {
		$this->addType('scopeId', 'integer');
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'uid' => $this->uid,
			'role' => $this->role,
			'scopeType' => $this->scopeType,
			'scopeId' => $this->scopeId,
			'grantedBy' => $this->grantedBy,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
