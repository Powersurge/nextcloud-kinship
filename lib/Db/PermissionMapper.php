<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCA\Kinship\Constants\PermissionRole;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Permission>
 */
class PermissionMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'kinship_permissions', Permission::class);
	}

	/**
	 * @return Permission[]
	 */
	public function findAll(): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName());

		return $this->findEntities($qb);
	}

	public function find(int $id): Permission {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	/**
	 * @return Permission[]
	 */
	public function findByUid(string $uid): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid)));

		return $this->findEntities($qb);
	}

	/**
	 * The role a user holds at a given scope, or null if none.
	 */
	public function findRole(string $uid, string $scopeType = PermissionRole::SCOPE_GLOBAL, ?int $scopeId = null): ?Permission {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid)))
			->andWhere($qb->expr()->eq('scope_type', $qb->createNamedParameter($scopeType)));

		if ($scopeId === null) {
			$qb->andWhere($qb->expr()->isNull('scope_id'));
		} else {
			$qb->andWhere($qb->expr()->eq('scope_id', $qb->createNamedParameter($scopeId, IQueryBuilder::PARAM_INT)));
		}

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException) {
			return null;
		}
	}

	public function insert(Permission|Entity $permission): Permission {
		return parent::insert($permission);
	}

	public function update(Permission|Entity $permission): Permission {
		return parent::update($permission);
	}

	public function delete(Permission|Entity $permission): Entity {
		return parent::delete($permission);
	}
}
