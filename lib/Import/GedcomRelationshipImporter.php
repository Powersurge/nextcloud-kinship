<?php

declare(strict_types=1);

namespace OCA\Kinship\Import;

use OCA\Kinship\Constants\RelationshipType;
use OCA\Kinship\Db\DateValue;
use OCA\Kinship\Db\Relationship;
use OCA\Kinship\Db\RelationshipMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

/**
 * Persists parent/spouse links extracted from GEDCOM families.
 */
class GedcomRelationshipImporter {
	/** @var array<string, true> */
	private array $created = [];

	public function __construct(
		private RelationshipMapper $mapper,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	public function create(
		int $personA,
		int $personB,
		string $type,
		?DateValue $date = null,
	): ?Relationship {
		if (!RelationshipType::isAcceptedInput($type)) {
			return null;
		}

		$canonical = RelationshipType::canonicalize($personA, $personB, $type);
		$key = $canonical['personA'] . '-' . $canonical['personB'] . '-' . $canonical['type'];

		if (isset($this->created[$key])) {
			return null;
		}

		$existing = $this->mapper->findCanonical(
			$canonical['personA'],
			$canonical['personB'],
			$canonical['type'],
		);

		if ($existing !== null) {
			$this->created[$key] = true;

			return $existing;
		}

		$now = $this->timeFactory->getTime();
		$uid = $this->userSession->getUser()?->getUID();

		$relationship = new Relationship();
		$relationship->setPersonA($canonical['personA']);
		$relationship->setPersonB($canonical['personB']);
		$relationship->setType($canonical['type']);
		if ($date !== null && !$date->isEmpty()) {
			$relationship->setDate($date);
		}
		$relationship->setOwnerUid($uid);
		$relationship->setCreatedBy($uid);
		$relationship->setCreatedAt($now);
		$relationship->setUpdatedAt($now);

		$saved = $this->mapper->insert($relationship);
		$this->created[$key] = true;

		return $saved;
	}
}
