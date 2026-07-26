<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_persons - individuals.
 *
 * Birth/death are core events (see kinship_events); the birth_/death_ columns
 * here are a denormalized cache kept in sync by the service layer for fast
 * display, sorting and living-status calculation.
 */
class Version0001Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		\Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_persons')) {
			$table = $schema->createTable('kinship_persons');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('first_name', 'string', ['length' => 100, 'notnull' => true]);
			$table->addColumn('middle_name', 'string', ['length' => 100, 'notnull' => false]);
			$table->addColumn('last_name', 'string', ['length' => 100, 'notnull' => true]);
			$table->addColumn('preferred_name', 'string', ['length' => 100, 'notnull' => false]);
			$table->addColumn('suffix', 'string', ['length' => 32, 'notnull' => false]);
			$table->addColumn('gender', 'string', ['length' => 32, 'notnull' => false]);
			$table->addColumn('living_status', 'string', [
				'length' => 16,
				'notnull' => true,
				'default' => 'unknown',
			]);
			$table->addColumn('birth_date_start', 'string', ['length' => 10, 'notnull' => false]);
			$table->addColumn('birth_date_display', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('death_date_start', 'string', ['length' => 10, 'notnull' => false]);
			$table->addColumn('death_date_display', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('biography', 'text', ['notnull' => false]);
			$table->addColumn('photo_file_id', 'integer', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_by', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('updated_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['last_name'], 'kinship_person_last_name_idx');
			$table->addIndex(['owner_uid'], 'kinship_person_owner_idx');
		}

		return $schema;
	}
}
