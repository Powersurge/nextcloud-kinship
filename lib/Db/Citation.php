<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getSourceId()
 * @method void setSourceId(int $sourceId)
 * @method string getSubjectType()
 * @method void setSubjectType(string $subjectType)
 * @method int getSubjectId()
 * @method void setSubjectId(int $subjectId)
 * @method ?string getDetail()
 * @method void setDetail(?string $detail)
 * @method ?string getTranscription()
 * @method void setTranscription(?string $transcription)
 * @method ?string getConfidence()
 * @method void setConfidence(?string $confidence)
 * @method ?string getAccessedDate()
 * @method void setAccessedDate(?string $accessedDate)
 * @method ?string getCreatedBy()
 * @method void setCreatedBy(?string $createdBy)
 * @method ?int getCreatedAt()
 * @method void setCreatedAt(?int $createdAt)
 * @method ?int getUpdatedAt()
 * @method void setUpdatedAt(?int $updatedAt)
 */
class Citation extends Entity implements JsonSerializable {
	protected int $sourceId = 0;
	protected string $subjectType = '';
	protected int $subjectId = 0;
	protected ?string $detail = null;
	protected ?string $transcription = null;
	protected ?string $confidence = null;
	protected ?string $accessedDate = null;
	protected ?string $createdBy = null;
	protected ?int $createdAt = null;
	protected ?int $updatedAt = null;

	public function __construct() {
		$this->addType('sourceId', 'integer');
		$this->addType('subjectId', 'integer');
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'sourceId' => $this->sourceId,
			'subjectType' => $this->subjectType,
			'subjectId' => $this->subjectId,
			'detail' => $this->detail,
			'transcription' => $this->transcription,
			'confidence' => $this->confidence,
			'accessedDate' => $this->accessedDate,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
