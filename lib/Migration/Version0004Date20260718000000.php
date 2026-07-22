<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

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
				'title',
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
				'type',
				'string',
				[
					'length' => 64,
					'notnull' => true,
				]
			);

			$table->addColumn(
				'event_date',
				'string',
				[
					'length' => 32,
					'notnull' => false,
				]
			);

			$table->addColumn(
				'location',
				'string',
				[
					'length' => 255,
					'notnull' => false,
				]
			);

			$table->addColumn(
				'family_id',
				'integer',
				[
					'notnull' => false,
					'unsigned' => true,
				]
			);

			$table->addColumn(
				'person_id',
				'integer',
				[
					'notnull' => false,
					'unsigned' => true,
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
				['person_id'],
				'kinship_event_person_idx'
			);

			$table->addIndex(
				['family_id'],
				'kinship_event_family_idx'
			);

			$table->addIndex(
				['event_date'],
				'kinship_event_date_idx'
			);

			$table->addIndex(
				['type'],
				'kinship_event_type_idx'
			);
		}

		return $schema;
	}
}
