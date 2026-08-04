<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * A named gallery owned by a person, family, or event.
 *
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method ?string getDescription()
 * @method void setDescription(?string $description)
 * @method string getOwnerType()
 * @method void setOwnerType(string $ownerType)
 * @method int getOwnerId()
 * @method void setOwnerId(int $ownerId)
 * @method ?int getCoverMediaId()
 * @method void setCoverMediaId(?int $coverMediaId)
 * @method ?string getOwnerUid()
 * @method void setOwnerUid(?string $ownerUid)
 * @method ?string getCreatedBy()
 * @method void setCreatedBy(?string $createdBy)
 * @method ?int getCreatedAt()
 * @method void setCreatedAt(?int $createdAt)
 * @method ?int getUpdatedAt()
 * @method void setUpdatedAt(?int $updatedAt)
 */
class Album extends Entity implements JsonSerializable {
	protected string $title = '';
	protected ?string $description = null;
	protected string $ownerType = '';
	protected int $ownerId = 0;
	protected ?int $coverMediaId = null;
	protected ?string $ownerUid = null;
	protected ?string $createdBy = null;
	protected ?int $createdAt = null;
	protected ?int $updatedAt = null;

	/**
	 * Transient cover file summary filled by AlbumService; not a DB column.
	 *
	 * @var array{fileId: int, name: string, path: string, mime: string, size: int, mtime: int}|null
	 */
	private ?array $coverFile = null;

	public function __construct() {
		$this->addType('ownerId', 'integer');
		$this->addType('coverMediaId', 'integer');
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
	}

	/**
	 * @param array{fileId: int, name: string, path: string, mime: string, size: int, mtime: int}|null $coverFile
	 */
	public function setCoverFile(?array $coverFile): void {
		$this->coverFile = $coverFile;
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'title' => $this->title,
			'description' => $this->description,
			'ownerType' => $this->ownerType,
			'ownerId' => $this->ownerId,
			'coverMediaId' => $this->coverMediaId,
			'coverFile' => $this->coverFile,
			'ownerUid' => $this->ownerUid,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
