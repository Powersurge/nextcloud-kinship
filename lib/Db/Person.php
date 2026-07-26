<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCA\Kinship\Constants\LivingStatus;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getFirstName()
 * @method void setFirstName(string $firstName)
 * @method ?string getMiddleName()
 * @method void setMiddleName(?string $middleName)
 * @method string getLastName()
 * @method void setLastName(string $lastName)
 * @method ?string getPreferredName()
 * @method void setPreferredName(?string $preferredName)
 * @method ?string getSuffix()
 * @method void setSuffix(?string $suffix)
 * @method ?string getGender()
 * @method void setGender(?string $gender)
 * @method string getLivingStatus()
 * @method void setLivingStatus(string $livingStatus)
 * @method ?string getBiography()
 * @method void setBiography(?string $biography)
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
class Person extends Entity implements JsonSerializable {
	protected string $firstName = '';
	protected ?string $middleName = null;
	protected string $lastName = '';
	protected ?string $preferredName = null;
	protected ?string $suffix = null;
	protected ?string $gender = null;
	protected string $livingStatus = LivingStatus::UNKNOWN;
	protected ?string $biography = null;
	protected ?int $photoFileId = null;
	protected ?string $ownerUid = null;
	protected ?string $createdBy = null;
	protected ?int $createdAt = null;
	protected ?int $updatedAt = null;

	/**
	 * Birth/death dates are not columns on kinship_persons: they belong to the
	 * person's birth/death events. These two are transient, filled in on read
	 * by PersonService::hydrateLifeDates() and never persisted (they are set
	 * outside the Entity magic setters, so no field is marked dirty).
	 */
	private ?DateValue $birthDate = null;
	private ?DateValue $deathDate = null;

	public function __construct() {
		$this->addType('photoFileId', 'integer');
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
	}

	public function getBirthDate(): ?DateValue {
		return $this->birthDate;
	}

	public function setBirthDate(?DateValue $birthDate): void {
		$this->birthDate = $birthDate;
	}

	public function getDeathDate(): ?DateValue {
		return $this->deathDate;
	}

	public function setDeathDate(?DateValue $deathDate): void {
		$this->deathDate = $deathDate;
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'firstName' => $this->firstName,
			'middleName' => $this->middleName,
			'lastName' => $this->lastName,
			'preferredName' => $this->preferredName,
			'suffix' => $this->suffix,
			'gender' => $this->gender,
			'livingStatus' => $this->livingStatus,
			'birthDate' => $this->birthDate,
			'deathDate' => $this->deathDate,
			'biography' => $this->biography,
			'photoFileId' => $this->photoFileId,
			'ownerUid' => $this->ownerUid,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
