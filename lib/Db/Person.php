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
 * @method ?string getBirthDateStart()
 * @method void setBirthDateStart(?string $birthDateStart)
 * @method ?string getBirthDateDisplay()
 * @method void setBirthDateDisplay(?string $birthDateDisplay)
 * @method ?string getDeathDateStart()
 * @method void setDeathDateStart(?string $deathDateStart)
 * @method ?string getDeathDateDisplay()
 * @method void setDeathDateDisplay(?string $deathDateDisplay)
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
	protected ?string $birthDateStart = null;
	protected ?string $birthDateDisplay = null;
	protected ?string $deathDateStart = null;
	protected ?string $deathDateDisplay = null;
	protected ?string $biography = null;
	protected ?int $photoFileId = null;
	protected ?string $ownerUid = null;
	protected ?string $createdBy = null;
	protected ?int $createdAt = null;
	protected ?int $updatedAt = null;

	public function __construct() {
		$this->addType('photoFileId', 'integer');
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
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
			'birthDate' => $this->birthDateStart !== null || $this->birthDateDisplay !== null
				? ['start' => $this->birthDateStart, 'display' => $this->birthDateDisplay]
				: null,
			'deathDate' => $this->deathDateStart !== null || $this->deathDateDisplay !== null
				? ['start' => $this->deathDateStart, 'display' => $this->deathDateDisplay]
				: null,
			'biography' => $this->biography,
			'photoFileId' => $this->photoFileId,
			'ownerUid' => $this->ownerUid,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
