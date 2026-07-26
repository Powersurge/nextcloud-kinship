<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_participants - persons taking part in an event, with a role.
 *
 * Replaces the old direct event.person_id column so an event (e.g. a marriage)
 * can involve several people in distinct roles.
 */
class Version0008Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		\Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_participants')) {
			$table = $schema->createTable('kinship_participants');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('event_id', 'integer', ['notnull' => true, 'unsigned' => true]);
			$table->addColumn('person_id', 'integer', ['notnull' => true, 'unsigned' => true]);
			$table->addColumn('role', 'string', ['length' => 32, 'notnull' => true, 'default' => 'subject']);
			$table->addColumn('note', 'string', ['length' => 255, 'notnull' => false]);
			$table->addColumn('created_by', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['event_id'], 'kinship_part_event_idx');
			$table->addIndex(['person_id'], 'kinship_part_person_idx');
			$table->addUniqueIndex(['event_id', 'person_id', 'role'], 'kinship_part_unique');
		}

		return $schema;
	}
}
