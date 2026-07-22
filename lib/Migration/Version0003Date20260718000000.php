<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

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

			$table->addColumn(
				'id',
				'integer',
				[
					'autoincrement' => true,
					'notnull' => true,
					'unsigned' => true,
				]
			);

			$table->addColumn(
				'person_a',
				'integer',
				[
					'notnull' => true,
					'unsigned' => true,
				]
			);

			$table->addColumn(
				'person_b',
				'integer',
				[
					'notnull' => true,
					'unsigned' => true,
				]
			);

			$table->addColumn(
				'type',
				'string',
				[
					'length' => 64,
					'notnull' => true,
				]
			);

			$table->addColumn(
				'start_date',
				'string',
				[
					'length' => 32,
					'notnull' => false,
				]
			);

			$table->addColumn(
				'end_date',
				'string',
				[
					'length' => 32,
					'notnull' => false,
				]
			);

			$table->setPrimaryKey(['id']);

			$table->addIndex(
				['person_a'],
				'kin_rel_person_a_idx'
			);

			$table->addIndex(
				['person_b'],
				'kin_rel_person_b_idx'
			);

			$table->addIndex(
				['type'],
				'kin_rel_type_idx'
			);
		}

		return $schema;
	}
}
