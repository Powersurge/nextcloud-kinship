<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Family extends Entity implements JsonSerializable {
	protected string $name = '';
	protected ?string $description = null;
	protected ?int $createdBy = null;

	public function __construct() {
		$this->addType('createdBy', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description,
			'createdBy' => $this->createdBy,
		];
	}
}
