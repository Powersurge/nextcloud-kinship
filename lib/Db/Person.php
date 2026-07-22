<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Person extends Entity implements JsonSerializable {
	protected string $firstName = '';
	protected ?string $middleName = '';
	protected string $lastName = '';
	protected ?string $preferredName = '';
	protected ?string $birthDate = null;
	protected ?string $deathDate = null;
	protected ?string $gender = null;
	protected bool $alive = true;
	protected ?string $biography = null;
	protected ?int $photoFileId = null;

	public function __construct() {
		$this->addType('alive', 'boolean');
		$this->addType('photoFileId', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'firstName' => $this->firstName,
			'middleName' => $this->middleName,
			'lastName' => $this->lastName,
			'preferredName' => $this->preferredName,
			'birthDate' => $this->birthDate,
			'deathDate' => $this->deathDate,
			'gender' => $this->gender,
			'alive' => $this->alive,
			'biography' => $this->biography,
			'photoFileId' => $this->photoFileId,
		];
	}
}
