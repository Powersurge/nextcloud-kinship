<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getName()
 * @method void setName(string $name)
 * @method ?string getFullName()
 * @method void setFullName(?string $fullName)
 * @method ?int getParentId()
 * @method void setParentId(?int $parentId)
 * @method ?string getLatitude()
 * @method void setLatitude(?string $latitude)
 * @method ?string getLongitude()
 * @method void setLongitude(?string $longitude)
 * @method ?string getType()
 * @method void setType(?string $type)
 * @method ?string getNotes()
 * @method void setNotes(?string $notes)
 * @method ?string getOwnerUid()
 * @method void setOwnerUid(?string $ownerUid)
 * @method ?string getCreatedBy()
 * @method void setCreatedBy(?string $createdBy)
 * @method ?int getCreatedAt()
 * @method void setCreatedAt(?int $createdAt)
 * @method ?int getUpdatedAt()
 * @method void setUpdatedAt(?int $updatedAt)
 */
class Place extends Entity implements JsonSerializable {
	protected string $name = '';
	protected ?string $fullName = null;
	protected ?int $parentId = null;
	protected ?string $latitude = null;
	protected ?string $longitude = null;
	protected ?string $type = null;
	protected ?string $notes = null;
	protected ?string $ownerUid = null;
	protected ?string $createdBy = null;
	protected ?int $createdAt = null;
	protected ?int $updatedAt = null;

	public function __construct() {
		$this->addType('parentId', 'integer');
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'fullName' => $this->fullName,
			'parentId' => $this->parentId,
			'latitude' => $this->latitude,
			'longitude' => $this->longitude,
			'type' => $this->type,
			'notes' => $this->notes,
			'ownerUid' => $this->ownerUid,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
