<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_events - dated occurrences.
 *
 * type is a core event type (birth, death, marriage, education, military,
 * custom); when custom, custom_event_id points at kinship_custom_events. The
 * date_* columns hold a genealogy date value object. Person links are made via
 * kinship_participants; family_id links shared family events (e.g. marriage).
 */
class Version0004Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		\Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_events')) {
			$table = $schema->createTable('kinship_events');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('type', 'string', ['length' => 32, 'notnull' => true]);
			$table->addColumn('custom_event_id', 'integer', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('title', 'string', ['length' => 255, 'notnull' => false]);
			$table->addColumn('description', 'text', ['notnull' => false]);
			$table->addColumn('place_id', 'integer', ['notnull' => false, 'unsigned' => true]);

			$table->addColumn('date_kind', 'string', ['length' => 16, 'notnull' => false]);
			$table->addColumn('date_precision', 'string', ['length' => 16, 'notnull' => false]);
			$table->addColumn('date_start_year', 'integer', ['length' => 4, 'notnull' => false]);
			$table->addColumn('date_start_month', 'integer', ['length' => 2, 'notnull' => false]);
			$table->addColumn('date_start_day', 'integer', ['length' => 2, 'notnull' => false]);

			$table->addColumn('date_end_year', 'integer', ['length' => 4, 'notnull' => false]);
			$table->addColumn('date_end_month', 'integer', ['length' => 2, 'notnull' => false]);
			$table->addColumn('date_end_day', 'integer', ['length' => 2, 'notnull' => false]);

			$table->addColumn('family_id', 'integer', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_by', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('updated_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['type'], 'kinship_event_type_idx');
			$table->addIndex(['place_id'], 'kinship_event_place_idx');
			$table->addIndex(['family_id'], 'kinship_event_family_idx');
			$table->addIndex(['owner_uid'], 'kinship_event_owner_idx');
		}

		return $schema;
	}
}
