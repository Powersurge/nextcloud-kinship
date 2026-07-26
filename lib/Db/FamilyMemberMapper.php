<?php

declare(strict_types=1);

namespace OCA\Kinship\Db;

use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<FamilyMember>
 */
class FamilyMemberMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'kinship_family_members', FamilyMember::class);
	}

	/**
	 * @return FamilyMember[]
	 */
	public function findByFamily(int $familyId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('family_id', $qb->createNamedParameter($familyId, IQueryBuilder::PARAM_INT)));

		return $this->findEntities($qb);
	}

	/**
	 * @return FamilyMember[]
	 */
	public function findByPerson(int $personId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('person_id', $qb->createNamedParameter($personId, IQueryBuilder::PARAM_INT)));

		return $this->findEntities($qb);
	}

	/**
	 * Remove every membership row of a family (before a resync).
	 */
	public function deleteByFamily(int $familyId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('family_id', $qb->createNamedParameter($familyId, IQueryBuilder::PARAM_INT)));
		$qb->executeStatement();
	}

	public function insert(FamilyMember|Entity $member): FamilyMember {
		return parent::insert($member);
	}

	public function delete(FamilyMember|Entity $member): Entity {
		return parent::delete($member);
	}
}
