<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getSubjectType()
 * @method void setSubjectType(string $subjectType)
 * @method int getSubjectId()
 * @method void setSubjectId(int $subjectId)
 * @method string getType()
 * @method void setType(string $type)
 * @method ?string getValue()
 * @method void setValue(?string $value)
 * @method ?int getPlaceId()
 * @method void setPlaceId(?int $placeId)
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
 * @method void setDateKind(?string $dateKind)
 * @method void setDateStart(?string $dateStart)
 * @method void setDateEnd(?string $dateEnd)
 * @method void setDateDisplay(?string $dateDisplay)
 */
class Fact extends Entity implements JsonSerializable {
	use DateValueTrait;

	protected string $subjectType = '';
	protected int $subjectId = 0;
	protected string $type = '';
	protected ?string $value = null;
	protected ?int $placeId = null;
	protected ?string $note = null;
	protected ?string $ownerUid = null;
	protected ?string $createdBy = null;
	protected ?int $createdAt = null;
	protected ?int $updatedAt = null;

	/**
	 * Transient place summary filled by FactService; not a DB column.
	 *
	 * @var array{id: int, name: string, fullName: ?string}|null
	 */
	private ?array $place = null;

	public function __construct() {
		$this->addType('subjectId', 'integer');
		$this->addType('placeId', 'integer');
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
	}

	/**
	 * @param array{id: int, name: string, fullName: ?string}|null $place
	 */
	public function setPlace(?array $place): void {
		$this->place = $place;
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'subjectType' => $this->subjectType,
			'subjectId' => $this->subjectId,
			'type' => $this->type,
			'value' => $this->value,
			'placeId' => $this->placeId,
			'place' => $this->place,
			'date' => $this->getDate(),
			'note' => $this->note,
			'ownerUid' => $this->ownerUid,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
