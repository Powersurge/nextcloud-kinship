<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_relationships - the canonical relationship graph.
 *
 * Only the ~dozen canonical types are stored (person_a is the parent/guardian/
 * ordered-spouse side); inverse labels are normalized on write and all other
 * kinship is computed. The date_* columns hold a genealogy date value object
 * (start/end for spouse/partner spans).
 */
class Version0003Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		\Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_relationships')) {
			$table = $schema->createTable('kinship_relationships');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('person_a', 'integer', ['notnull' => true, 'unsigned' => true]);
			$table->addColumn('person_b', 'integer', ['notnull' => true, 'unsigned' => true]);
			$table->addColumn('type', 'string', ['length' => 32, 'notnull' => true]);
			$table->addColumn('note', 'text', ['notnull' => false]);
			$table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_by', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('updated_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['person_a'], 'kin_rel_person_a_idx');
			$table->addIndex(['person_b'], 'kin_rel_person_b_idx');
			$table->addIndex(['type'], 'kin_rel_type_idx');
			$table->addUniqueIndex(['person_a', 'person_b', 'type'], 'kin_rel_unique');
		}

		return $schema;
	}
}
