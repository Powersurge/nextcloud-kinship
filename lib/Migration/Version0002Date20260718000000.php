<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_families - household / partnership units.
 *
 * Membership is derived from relationships (see kinship_family_members); a
 * family row only carries family-level shared information (name, notes) and is
 * usually auto-created (is_derived = true).
 */
class Version0002Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		\Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_families')) {
			$table = $schema->createTable('kinship_families');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('name', 'string', ['length' => 255, 'notnull' => false]);
			$table->addColumn('type', 'string', ['length' => 32, 'notnull' => false]);
			$table->addColumn('description', 'text', ['notnull' => false]);
			$table->addColumn('is_derived', 'boolean', ['notnull' => false, 'default' => true]);
			$table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_by', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('updated_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['owner_uid'], 'kinship_family_owner_idx');
		}

		return $schema;
	}
}
