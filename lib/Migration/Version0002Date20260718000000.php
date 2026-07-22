<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

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
				'name',
				'string',
				[
					'length' => 255,
					'notnull' => true,
				]
			);

			$table->addColumn(
				'description',
				'text',
				[
					'notnull' => false,
				]
			);

			$table->addColumn(
				'created_by',
				'integer',
				[
					'notnull' => false,
					'unsigned' => true,
				]
			);

			$table->setPrimaryKey(['id']);

			$table->addIndex(
				['created_by'],
				'kinship_family_created_by_idx'
			);
		}

		return $schema;
	}
}
