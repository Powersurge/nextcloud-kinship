<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * A reference linking a Nextcloud file to a genealogy object.
 *
 * @method int getFileId()
 * @method void setFileId(int $fileId)
 * @method string getSubjectType()
 * @method void setSubjectType(string $subjectType)
 * @method int getSubjectId()
 * @method void setSubjectId(int $subjectId)
 * @method ?int getAlbumId()
 * @method void setAlbumId(?int $albumId)
 * @method int getSortOrder()
 * @method void setSortOrder(int $sortOrder)
 * @method ?string getCaption()
 * @method void setCaption(?string $caption)
 * @method bool getIsPrimary()
 * @method void setIsPrimary(bool $isPrimary)
 * @method ?string getOwnerUid()
 * @method void setOwnerUid(?string $ownerUid)
 * @method ?string getCreatedBy()
 * @method void setCreatedBy(?string $createdBy)
 * @method ?int getCreatedAt()
 * @method void setCreatedAt(?int $createdAt)
 */
class Media extends Entity implements JsonSerializable {
	protected int $fileId = 0;
	protected string $subjectType = '';
	protected int $subjectId = 0;
	protected ?int $albumId = null;
	protected int $sortOrder = 0;
	protected ?string $caption = null;
	protected bool $isPrimary = false;
	protected ?string $ownerUid = null;
	protected ?string $createdBy = null;
	protected ?int $createdAt = null;

	/**
	 * Transient file summary filled by MediaService; not a DB column.
	 *
	 * @var array{fileId: int, name: string, path: string, mime: string, size: int, mtime: int}|null
	 */
	private ?array $file = null;

	public function __construct() {
		$this->addType('fileId', 'integer');
		$this->addType('subjectId', 'integer');
		$this->addType('albumId', 'integer');
		$this->addType('sortOrder', 'integer');
		$this->addType('isPrimary', 'boolean');
		$this->addType('createdAt', 'integer');
	}

	/**
	 * @param array{fileId: int, name: string, path: string, mime: string, size: int, mtime: int}|null $file
	 */
	public function setFile(?array $file): void {
		$this->file = $file;
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'fileId' => $this->fileId,
			'file' => $this->file,
			'subjectType' => $this->subjectType,
			'subjectId' => $this->subjectId,
			'albumId' => $this->albumId,
			'sortOrder' => $this->sortOrder,
			'caption' => $this->caption,
			'isPrimary' => $this->isPrimary,
			'ownerUid' => $this->ownerUid,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt,
		];
	}
}
