<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Relationship extends Entity implements JsonSerializable {
	protected int $personA = 0;
	protected int $personB = 0;
	protected string $type = '';
	protected ?string $startDate = null;
	protected ?string $endDate = null;

	public function __construct() {
		$this->addType('personA', 'integer');
		$this->addType('personB', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'personA' => $this->personA,
			'personB' => $this->personB,
			'type' => $this->type,
			'startDate' => $this->startDate,
			'endDate' => $this->endDate,
		];
	}
}
