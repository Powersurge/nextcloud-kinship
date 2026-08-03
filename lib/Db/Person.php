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
 * @method ?DateValue getBirthDate()
 * @method ?DateValue getBirthDate()
 * @method void setEvents(array $events)
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
	protected ?string $lastName = null;
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
	 * Birth/death dates and places are not columns on kinship_persons: they
	 * belong to the person's birth/death events. These are transient, filled
	 * in on read by PersonService::hydrateLifeDates() and never persisted
	 * (they are set outside the Entity magic setters, so no field is marked
	 * dirty).
	 */
	private ?DateValue $birthDate = null;
	private ?DateValue $deathDate = null;

	/** @var array{id: int, name: string, fullName: ?string}|null */
	private ?array $birthPlace = null;

	/** @var array{id: int, name: string, fullName: ?string}|null */
	private ?array $deathPlace = null;

	/** Transient life-event ids for citing birth/death. */
	private ?int $birthEventId = null;
	private ?int $deathEventId = null;

	public function __construct() {
		$this->addType('photoFileId', 'integer');
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
	}

	public function setBirthDate(?DateValue $birthDate): void {
		$this->birthDate = $birthDate;
	}

	public function setDeathDate(?DateValue $deathDate): void {
		$this->deathDate = $deathDate;
	}

	/**
	 * @param array{id: int, name: string, fullName: ?string}|null $birthPlace
	 */
	public function setBirthPlace(?array $birthPlace): void {
		$this->birthPlace = $birthPlace;
	}

	/**
	 * @param array{id: int, name: string, fullName: ?string}|null $deathPlace
	 */
	public function setDeathPlace(?array $deathPlace): void {
		$this->deathPlace = $deathPlace;
	}

	public function setBirthEventId(?int $birthEventId): void {
		$this->birthEventId = $birthEventId;
	}

	public function setDeathEventId(?int $deathEventId): void {
		$this->deathEventId = $deathEventId;
	}

	public function getFullName(): string {
		return implode(' ', array_filter([
			$this->firstName,
			$this->middleName,
			$this->lastName
		], fn ($value) => !empty($value)));
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'firstName' => $this->firstName,
			'middleName' => $this->middleName,
			'lastName' => $this->lastName,
			'fullName' => $this->getFullName(),
			'preferredName' => $this->preferredName,
			'suffix' => $this->suffix,
			'gender' => $this->gender,
			'livingStatus' => $this->livingStatus,
			'birthDate' => $this->birthDate,
			'deathDate' => $this->deathDate,
			'birthPlace' => $this->birthPlace,
			'deathPlace' => $this->deathPlace,
			'birthEventId' => $this->birthEventId,
			'deathEventId' => $this->deathEventId,
			'biography' => $this->biography,
			'photoFileId' => $this->photoFileId,
			'ownerUid' => $this->ownerUid,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
