<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use JsonSerializable;
use OCA\Kinship\Constants\ParticipantRole;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getEventId()
 * @method void setEventId(int $eventId)
 * @method int getPersonId()
 * @method void setPersonId(int $personId)
 * @method string getRole()
 * @method void setRole(string $role)
 * @method ?string getNote()
 * @method void setNote(?string $note)
 * @method ?string getCreatedBy()
 * @method void setCreatedBy(?string $createdBy)
 * @method ?int getCreatedAt()
 * @method void setCreatedAt(?int $createdAt)
 */
class Participant extends Entity implements JsonSerializable {
	protected int $eventId = 0;
	protected int $personId = 0;
	protected string $role = ParticipantRole::SUBJECT;
	protected ?string $note = null;
	protected ?string $createdBy = null;
	protected ?int $createdAt = null;

	public function __construct() {
		$this->addType('eventId', 'integer');
		$this->addType('personId', 'integer');
		$this->addType('createdAt', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'eventId' => $this->eventId,
			'personId' => $this->personId,
			'role' => $this->role,
			'note' => $this->note,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt,
		];
	}
}
