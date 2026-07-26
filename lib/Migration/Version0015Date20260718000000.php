<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_custom_events - owner-defined event type labels used when an event's
 * core type is "custom". The table exists in v0.1; the owner-only management
 * UI arrives in v0.2.
 */
class Version0015Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		\Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_custom_events')) {
			$table = $schema->createTable('kinship_custom_events');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('name', 'string', ['length' => 64, 'notnull' => true]);
			$table->addColumn('description', 'text', ['notnull' => false]);
			$table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => true]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('updated_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['owner_uid', 'name'], 'kinship_custom_evt_unique');
		}

		return $schema;
	}
}
