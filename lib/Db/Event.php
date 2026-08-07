<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getType()
 * @method void setType(string $type)
 * @method ?int getCustomEventId()
 * @method void setCustomEventId(?int $customEventId)
 * @method ?string getTitle()
 * @method void setTitle(?string $title)
 * @method ?string getDescription()
 * @method void setDescription(?string $description)
 * @method ?int getPlaceId()
 * @method void setPlaceId(?int $placeId)
 * @method ?int getFamilyId()
 * @method void setFamilyId(?int $familyId)
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
 * @method void setDateKind(?string $dateKind)
 * @method void setDatePrecision(?string $datePrecision)
 * @method void setDateStartYear(?string $dateStartYear)
 * @method void setDateStartMonth(?string $dateStartMonth)
 * @method void setDateStartDay(?string $dateStartDay)
 * @method void setDateEndYear(?string $dateEndYear)
 * @method void setDateEndMonth(?string $dateEndMonth)
 * @method void setDateEndDay(?string $dateEndDay)
 */
class Event extends Entity implements JsonSerializable {
	use DateValueTrait;

	protected string $type = '';
	protected ?int $customEventId = null;
	protected ?string $title = null;
	protected ?string $description = null;
	protected ?int $placeId = null;
	protected ?int $familyId = null;
	protected ?int $photoFileId = null;
	protected ?string $ownerUid = null;
	protected ?string $createdBy = null;
	protected ?int $createdAt = null;
	protected ?int $updatedAt = null;

	/**
	 * Transient place summary filled by EventService; not a DB column.
	 *
	 * @var array{id: int, name: string, fullName: ?string}|null
	 */
	private ?array $place = null;

	public function __construct() {
		$this->addType('customEventId', 'integer');
		$this->addType('placeId', 'integer');
		$this->addType('familyId', 'integer');
		$this->addType('photoFileId', 'integer');
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
			'type' => $this->type,
			'customEventId' => $this->customEventId,
			'title' => $this->title,
			'description' => $this->description,
			'placeId' => $this->placeId,
			'place' => $this->place,
			'date' => $this->getDate(),
			'familyId' => $this->familyId,
			'photoFileId' => $this->photoFileId,
			'ownerUid' => $this->ownerUid,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
