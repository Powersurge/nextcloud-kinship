<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * A canonical relationship row (person_a is the parent/guardian/ordered-spouse
 * side). See {@see \OCA\Kinship\Constants\RelationshipType}.
 *
 * @method int getPersonA()
 * @method void setPersonA(int $personA)
 * @method int getPersonB()
 * @method void setPersonB(int $personB)
 * @method string getType()
 * @method void setType(string $type)
 * @method ?string getNote()
 * @method void setNote(?string $note)
 * @method ?string getOwnerUid()
 * @method void setOwnerUid(?string $ownerUid)
 * @method ?string getCreatedBy()
 * @method void setCreatedBy(?string $createdBy)
 * @method ?int getCreatedAt()
 * @method void setCreatedAt(?int $createdAt)
 * @method ?int getUpdatedAt()
 * @method void setUpdatedAt(?int $updatedAt)
 */
class Relationship extends Entity implements JsonSerializable {
	use DateValueTrait;

	protected int $personA = 0;
	protected int $personB = 0;
	protected string $type = '';
	protected ?string $note = null;
	protected ?string $ownerUid = null;
	protected ?string $createdBy = null;
	protected ?int $createdAt = null;
	protected ?int $updatedAt = null;

	public function __construct() {
		$this->addType('personA', 'integer');
		$this->addType('personB', 'integer');
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'personA' => $this->personA,
			'personB' => $this->personB,
			'type' => $this->type,
			'date' => $this->getDate(),
			'note' => $this->note,
			'ownerUid' => $this->ownerUid,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
