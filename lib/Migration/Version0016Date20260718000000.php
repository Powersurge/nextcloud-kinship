<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_permissions - core permissions model.
 *
 * Records role grants (owner/editor/contributor/viewer). scope_type is global
 * in v0.1; object-level scoping (scope_id) and actual enforcement land with
 * sharing in v0.2.
 */
class Version0016Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		\Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_permissions')) {
			$table = $schema->createTable('kinship_permissions');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('uid', 'string', ['length' => 64, 'notnull' => true]);
			$table->addColumn('role', 'string', ['length' => 16, 'notnull' => true]);
			$table->addColumn('scope_type', 'string', ['length' => 16, 'notnull' => true, 'default' => 'global']);
			$table->addColumn('scope_id', 'integer', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('granted_by', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('updated_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['uid'], 'kinship_perm_uid_idx');
			$table->addUniqueIndex(['uid', 'scope_type', 'scope_id'], 'kinship_perm_unique');
		}

		return $schema;
	}
}
