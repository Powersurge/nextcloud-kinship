<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method ?string getAuthor()
 * @method void setAuthor(?string $author)
 * @method ?string getPublication()
 * @method void setPublication(?string $publication)
 * @method ?string getRepository()
 * @method void setRepository(?string $repository)
 * @method ?string getSourceType()
 * @method void setSourceType(?string $sourceType)
 * @method ?string getUrl()
 * @method void setUrl(?string $url)
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
class Source extends Entity implements JsonSerializable {
	protected string $title = '';
	protected ?string $author = null;
	protected ?string $publication = null;
	protected ?string $repository = null;
	protected ?string $sourceType = null;
	protected ?string $url = null;
	protected ?string $notes = null;
	protected ?string $ownerUid = null;
	protected ?string $createdBy = null;
	protected ?int $createdAt = null;
	protected ?int $updatedAt = null;

	public function __construct() {
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'title' => $this->title,
			'author' => $this->author,
			'publication' => $this->publication,
			'repository' => $this->repository,
			'sourceType' => $this->sourceType,
			'url' => $this->url,
			'notes' => $this->notes,
			'ownerUid' => $this->ownerUid,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
