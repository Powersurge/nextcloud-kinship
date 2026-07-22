<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

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
				'first_name',
				'string',
				[
					'length' => 100,
					'notnull' => true,
				]
			);

			$table->addColumn(
				'middle_name',
				'string',
				[
					'length' => 100,
					'notnull' => false,
				]
			);

			$table->addColumn(
				'last_name',
				'string',
				[
					'length' => 100,
					'notnull' => true,
				]
			);

			$table->addColumn(
				'preferred_name',
				'string',
				[
					'length' => 100,
					'notnull' => false,
				]
			);

			$table->addColumn(
				'birth_date',
				'string',
				[
					'length' => 32,
					'notnull' => false,
				]
			);

			$table->addColumn(
				'death_date',
				'string',
				[
					'length' => 32,
					'notnull' => false,
				]
			);

			$table->addColumn(
				'gender',
				'string',
				[
					'length' => 32,
					'notnull' => false,
				]
			);

			$table->addColumn(
				'alive',
				'boolean',
				[
					'notnull' => false,
					'default' => true,
				]
			);

			$table->addColumn(
				'biography',
				'text',
				[
					'notnull' => false,
				]
			);

			$table->addColumn(
				'photo_file_id',
				'integer',
				[
					'notnull' => false,
					'unsigned' => true,
				]
			);

			$table->setPrimaryKey(['id']);

			$table->addIndex(
				['last_name'],
				'kinship_person_last_name_idx'
			);
		}

		return $schema;
	}
}
