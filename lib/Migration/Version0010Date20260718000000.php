<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_facts - non-event assertions about a person, relationship or family
 * (occupation, religion, nationality, residence, physical description, name
 * variant, ...). Carries an optional place and a genealogy date value object.
 */
class Version0010Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		\Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_facts')) {
			$table = $schema->createTable('kinship_facts');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('subject_type', 'string', ['length' => 16, 'notnull' => true]);
			$table->addColumn('subject_id', 'integer', ['notnull' => true, 'unsigned' => true]);
			$table->addColumn('type', 'string', ['length' => 48, 'notnull' => true]);
			$table->addColumn('value', 'text', ['notnull' => false]);
			$table->addColumn('place_id', 'integer', ['notnull' => false, 'unsigned' => true]);

			$table->addColumn('date_kind', 'string', ['length' => 16, 'notnull' => false]);
			$table->addColumn('date_precision', 'string', ['length' => 16, 'notnull' => false]);
			$table->addColumn('date_start_year', 'integer', ['length' => 4, 'notnull' => false]);
			$table->addColumn('date_start_month', 'integer', ['length' => 2, 'notnull' => false]);
			$table->addColumn('date_start_day', 'integer', ['length' => 2, 'notnull' => false]);

			$table->addColumn('date_end_year', 'integer', ['length' => 4, 'notnull' => false]);
			$table->addColumn('date_end_month', 'integer', ['length' => 2, 'notnull' => false]);
			$table->addColumn('date_end_day', 'integer', ['length' => 2, 'notnull' => false]);

			$table->addColumn('note', 'text', ['notnull' => false]);
			$table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_by', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('updated_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['subject_type', 'subject_id'], 'kinship_fact_subject_idx');
			$table->addIndex(['type'], 'kinship_fact_type_idx');
		}

		return $schema;
	}
}
