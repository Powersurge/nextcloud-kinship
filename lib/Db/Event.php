<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Event extends Entity implements JsonSerializable {
	protected string $title = '';
	protected ?string $description = null;
	protected string $type = '';
	protected ?string $eventDate = null;
	protected ?string $location = null;
	protected ?int $familyId = null;
	protected ?int $personId = null;
	protected ?int $createdBy = null;

	public function __construct() {
		$this->addType('familyId', 'integer');
		$this->addType('personId', 'integer');
		$this->addType('createdBy', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'title' => $this->title,
			'description' => $this->description,
			'type' => $this->type,
			'eventDate' => $this->eventDate,
			'location' => $this->location,
			'familyId' => $this->familyId,
			'personId' => $this->personId,
			'createdBy' => $this->createdBy,
		];
	}

	public function getPersonId(): ?int {
		return $this->personId;
	}

	public function setPersonId(
		?int $personId,
	): void {

		$this->personId = $personId;

	}

	public function getFamilyId(): ?int {
		return $this->familyId;
	}

	public function setFamilyId(
		?int $familyId,
	): void {

		$this->familyId = $familyId;

	}
}
