<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Db\CustomEvent;
use OCA\Kinship\Db\CustomEventMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IUserSession;

/**
 * Owner-defined custom event types. The owner-only management UI is a v0.2
 * feature; the table and basic CRUD exist in v0.1 so events of type "custom"
 * can reference a label.
 */
class CustomEventService {
	public function __construct(
		private CustomEventMapper $mapper,
		private IUserSession $userSession,
		private ITimeFactory $timeFactory,
	) {
	}

	/**
	 * @return CustomEvent[]
	 */
	public function getAll(): array {
		return $this->mapper->findAll();
	}

	public function get(int $id): CustomEvent {
		return $this->mapper->find($id);
	}

	public function create(array $data): CustomEvent {
		$now = $this->timeFactory->getTime();

		$customEvent = new CustomEvent();
		$customEvent->setName($data['name'] ?? '');
		$customEvent->setDescription($data['description'] ?? null);
		$customEvent->setOwnerUid($this->userSession->getUser()?->getUID() ?? '');
		$customEvent->setCreatedAt($now);
		$customEvent->setUpdatedAt($now);

		return $this->mapper->insert($customEvent);
	}

	public function update(int $id, array $data): CustomEvent {
		$customEvent = $this->mapper->find($id);

		if (array_key_exists('name', $data)) {
			$customEvent->setName($data['name']);
		}
		if (array_key_exists('description', $data)) {
			$customEvent->setDescription($data['description']);
		}

		$customEvent->setUpdatedAt($this->timeFactory->getTime());

		return $this->mapper->update($customEvent);
	}

	public function delete(int $id): void {
		$this->mapper->delete($this->mapper->find($id));
	}
}
