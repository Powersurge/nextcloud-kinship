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
	protected ?string $caption = null;
	protected bool $isPrimary = false;
	protected ?string $ownerUid = null;
	protected ?string $createdBy = null;
	protected ?int $createdAt = null;

	public function __construct() {
		$this->addType('fileId', 'integer');
		$this->addType('subjectId', 'integer');
		$this->addType('isPrimary', 'boolean');
		$this->addType('createdAt', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'fileId' => $this->fileId,
			'subjectType' => $this->subjectType,
			'subjectId' => $this->subjectId,
			'caption' => $this->caption,
			'isPrimary' => $this->isPrimary,
			'ownerUid' => $this->ownerUid,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt,
		];
	}
}
