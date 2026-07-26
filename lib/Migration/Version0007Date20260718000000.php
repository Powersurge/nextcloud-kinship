<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_places - hierarchical place gazetteer.
 *
 * Owner-editable (enforced in v0.2). parent_id builds the hierarchy
 * (city -> region -> country). Referenced by events and facts via place_id.
 */
class Version0007Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		\Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_places')) {
			$table = $schema->createTable('kinship_places');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('name', 'string', ['length' => 255, 'notnull' => true]);
			$table->addColumn('full_name', 'string', ['length' => 512, 'notnull' => false]);
			$table->addColumn('parent_id', 'integer', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('latitude', 'string', ['length' => 32, 'notnull' => false]);
			$table->addColumn('longitude', 'string', ['length' => 32, 'notnull' => false]);
			$table->addColumn('type', 'string', ['length' => 32, 'notnull' => false]);
			$table->addColumn('notes', 'text', ['notnull' => false]);
			$table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_by', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('updated_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['parent_id'], 'kinship_place_parent_idx');
			$table->addIndex(['owner_uid'], 'kinship_place_owner_idx');
			$table->addIndex(['name'], 'kinship_place_name_idx');
		}

		return $schema;
	}
}
