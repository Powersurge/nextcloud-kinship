<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Media>
 */
class MediaMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'kinship_media', Media::class);
	}

	public function find(int $id): Media {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	public function findOrNull(int $id): ?Media {
		try {
			return $this->find($id);
		} catch (DoesNotExistException) {
			return null;
		}
	}

	/**
	 * @return Media[]
	 */
	public function findBySubject(string $subjectType, int $subjectId, ?int $albumId = null): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('subject_type', $qb->createNamedParameter($subjectType)))
			->andWhere($qb->expr()->eq('subject_id', $qb->createNamedParameter($subjectId, IQueryBuilder::PARAM_INT)));

		if ($albumId === null) {
			// no album filter
		} elseif ($albumId === 0) {
			$qb->andWhere($qb->expr()->isNull('album_id'));
		} else {
			$qb->andWhere($qb->expr()->eq('album_id', $qb->createNamedParameter($albumId, IQueryBuilder::PARAM_INT)));
		}

		$qb->orderBy('sort_order', 'ASC')
			->addOrderBy('id', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * @return Media[]
	 */
	public function findByAlbum(int $albumId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('album_id', $qb->createNamedParameter($albumId, IQueryBuilder::PARAM_INT)))
			->orderBy('sort_order', 'ASC')
			->addOrderBy('id', 'ASC');

		return $this->findEntities($qb);
	}

	public function clearPrimaryForSubject(string $subjectType, int $subjectId, ?int $exceptId = null): void {
		$qb = $this->db->getQueryBuilder();
		$qb->update($this->getTableName())
			->set('is_primary', $qb->createNamedParameter(false, IQueryBuilder::PARAM_BOOL))
			->where($qb->expr()->eq('subject_type', $qb->createNamedParameter($subjectType)))
			->andWhere($qb->expr()->eq('subject_id', $qb->createNamedParameter($subjectId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('is_primary', $qb->createNamedParameter(true, IQueryBuilder::PARAM_BOOL)));

		if ($exceptId !== null) {
			$qb->andWhere($qb->expr()->neq('id', $qb->createNamedParameter($exceptId, IQueryBuilder::PARAM_INT)));
		}

		$qb->executeStatement();
	}

	public function deleteBySubject(string $subjectType, int $subjectId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('subject_type', $qb->createNamedParameter($subjectType)))
			->andWhere($qb->expr()->eq('subject_id', $qb->createNamedParameter($subjectId, IQueryBuilder::PARAM_INT)));
		$qb->executeStatement();
	}

	public function deleteByAlbum(int $albumId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('album_id', $qb->createNamedParameter($albumId, IQueryBuilder::PARAM_INT)));
		$qb->executeStatement();
	}

	public function clearCoverReferences(int $mediaId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->update('kinship_albums')
			->set('cover_media_id', $qb->createNamedParameter(null, IQueryBuilder::PARAM_NULL))
			->where($qb->expr()->eq('cover_media_id', $qb->createNamedParameter($mediaId, IQueryBuilder::PARAM_INT)));
		$qb->executeStatement();
	}

	public function insert(Media|Entity $media): Media {
		return parent::insert($media);
	}

	public function update(Media|Entity $media): Media {
		return parent::update($media);
	}

	public function delete(Media|Entity $media): Entity {
		return parent::delete($media);
	}
}
