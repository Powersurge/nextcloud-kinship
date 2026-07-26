<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCA\Kinship\Constants\ClaimStatus;
use OCP\AppFramework\Db\Entity;

/**
 * A proposed change pending review. subjectId is null when proposing a new
 * record; proposedValue holds a JSON payload.
 *
 * @method string getSubjectType()
 * @method void setSubjectType(string $subjectType)
 * @method ?int getSubjectId()
 * @method void setSubjectId(?int $subjectId)
 * @method ?string getField()
 * @method void setField(?string $field)
 * @method ?string getProposedValue()
 * @method void setProposedValue(?string $proposedValue)
 * @method string getOperation()
 * @method void setOperation(string $operation)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method ?string getConfidence()
 * @method void setConfidence(?string $confidence)
 * @method ?string getRationale()
 * @method void setRationale(?string $rationale)
 * @method ?int getSourceId()
 * @method void setSourceId(?int $sourceId)
 * @method ?string getCreatedBy()
 * @method void setCreatedBy(?string $createdBy)
 * @method ?string getReviewedBy()
 * @method void setReviewedBy(?string $reviewedBy)
 * @method ?int getReviewedAt()
 * @method void setReviewedAt(?int $reviewedAt)
 * @method ?int getCreatedAt()
 * @method void setCreatedAt(?int $createdAt)
 * @method ?int getUpdatedAt()
 * @method void setUpdatedAt(?int $updatedAt)
 */
class Claim extends Entity implements JsonSerializable {
	protected string $subjectType = '';
	protected ?int $subjectId = null;
	protected ?string $field = null;
	protected ?string $proposedValue = null;
	protected string $operation = ClaimStatus::OP_UPDATE;
	protected string $status = ClaimStatus::PENDING;
	protected ?string $confidence = null;
	protected ?string $rationale = null;
	protected ?int $sourceId = null;
	protected ?string $createdBy = null;
	protected ?string $reviewedBy = null;
	protected ?int $reviewedAt = null;
	protected ?int $createdAt = null;
	protected ?int $updatedAt = null;

	public function __construct() {
		$this->addType('subjectId', 'integer');
		$this->addType('sourceId', 'integer');
		$this->addType('reviewedAt', 'integer');
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'subjectType' => $this->subjectType,
			'subjectId' => $this->subjectId,
			'field' => $this->field,
			'proposedValue' => $this->proposedValue,
			'operation' => $this->operation,
			'status' => $this->status,
			'confidence' => $this->confidence,
			'rationale' => $this->rationale,
			'sourceId' => $this->sourceId,
			'createdBy' => $this->createdBy,
			'reviewedBy' => $this->reviewedBy,
			'reviewedAt' => $this->reviewedAt,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
