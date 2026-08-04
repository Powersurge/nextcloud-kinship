<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Album>
 */
class AlbumMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'kinship_albums', Album::class);
	}

	public function find(int $id): Album {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	public function findOrNull(int $id): ?Album {
		try {
			return $this->find($id);
		} catch (DoesNotExistException) {
			return null;
		}
	}

	/**
	 * @return Album[]
	 */
	public function findByOwner(string $ownerType, int $ownerId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('owner_type', $qb->createNamedParameter($ownerType)))
			->andWhere($qb->expr()->eq('owner_id', $qb->createNamedParameter($ownerId, IQueryBuilder::PARAM_INT)))
			->orderBy('title', 'ASC');

		return $this->findEntities($qb);
	}

	public function deleteByOwner(string $ownerType, int $ownerId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('owner_type', $qb->createNamedParameter($ownerType)))
			->andWhere($qb->expr()->eq('owner_id', $qb->createNamedParameter($ownerId, IQueryBuilder::PARAM_INT)));
		$qb->executeStatement();
	}

	public function insert(Album|Entity $album): Album {
		return parent::insert($album);
	}

	public function update(Album|Entity $album): Album {
		return parent::update($album);
	}

	public function delete(Album|Entity $album): Entity {
		return parent::delete($album);
	}
}
