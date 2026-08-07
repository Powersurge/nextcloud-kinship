<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method ?string getName()
 * @method void setName(?string $name)
 * @method ?string getType()
 * @method void setType(?string $type)
 * @method ?string getDescription()
 * @method void setDescription(?string $description)
 * @method bool getIsDerived()
 * @method void setIsDerived(bool $isDerived)
 * @method ?int getPhotoFileId()
 * @method void setPhotoFileId(?int $photoFileId)
 * @method ?string getOwnerUid()
 * @method void setOwnerUid(?string $ownerUid)
 * @method ?string getCreatedBy()
 * @method void setCreatedBy(?string $createdBy)
 * @method ?int getCreatedAt()
 * @method void setCreatedAt(?int $createdAt)
 * @method ?int getUpdatedAt()
 * @method void setUpdatedAt(?int $updatedAt)
 */
class Family extends Entity implements JsonSerializable {
	protected ?string $name = null;
	protected ?string $type = null;
	protected ?string $description = null;
	protected bool $isDerived = true;
	protected ?int $photoFileId = null;
	protected ?string $ownerUid = null;
	protected ?string $createdBy = null;
	protected ?int $createdAt = null;
	protected ?int $updatedAt = null;

	public function __construct() {
		$this->addType('isDerived', 'boolean');
		$this->addType('photoFileId', 'integer');
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'type' => $this->type,
			'description' => $this->description,
			'isDerived' => $this->isDerived,
			'photoFileId' => $this->photoFileId,
			'ownerUid' => $this->ownerUid,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
