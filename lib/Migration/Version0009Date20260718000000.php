<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_family_members - derived family membership.
 *
 * Auto-maintained by FamilyMembershipService from partnership + parent
 * relationships. role is parent or child. Never edited directly by users.
 */
class Version0009Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		\Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_family_members')) {
			$table = $schema->createTable('kinship_family_members');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('family_id', 'integer', ['notnull' => true, 'unsigned' => true]);
			$table->addColumn('person_id', 'integer', ['notnull' => true, 'unsigned' => true]);
			$table->addColumn('role', 'string', ['length' => 16, 'notnull' => false]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['family_id'], 'kinship_fam_mem_family_idx');
			$table->addIndex(['person_id'], 'kinship_fam_mem_person_idx');
			$table->addUniqueIndex(['family_id', 'person_id', 'role'], 'kinship_fam_mem_unique');
		}

		return $schema;
	}
}
