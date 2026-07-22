<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0005Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {

		$schema = $schemaClosure();
		/*
		 * Registered Kinship extensions
		 */
		if (!$schema->hasTable('kinship_extensions')) {

			$table = $schema->createTable(
				'kinship_extensions'
			);

			$table->addColumn(
				'id',
				'integer',
				[
					'autoincrement' => true,
					'notnull' => true
				]
			);

			$table->addColumn(
				'extension_id',
				'string',
				[
					'length' => 64,
					'notnull' => true
				]
			);

			$table->addColumn(
				'name',
				'string',
				[
					'length' => 128,
					'notnull' => true
				]
			);

			$table->addColumn(
				'description',
				'text',
				[
					'notnull' => false
				]
			);

			$table->addColumn(
				'version',
				'string',
				[
					'length' => 32,
					'notnull' => true
				]
			);

			$table->addColumn(
				'enabled',
				'boolean',
				[
					'notnull' => false,
					'default' => false,
				]
			);

			$table->addColumn(
				'created_at',
				'integer',
				[
					'notnull' => true
				]
			);

			$table->setPrimaryKey(
				['id']
			);

			$table->addUniqueIndex(
				['extension_id'],
				'kinship_ext_unique'
			);
		}

		/*
		 * Extension configuration storage
		 *
		 * Allows extensions to keep small
		 * settings without creating their
		 * own table.
		 */
		if (!$schema->hasTable('kin_ext_config')) {

			$table = $schema->createTable(
				'kin_ext_config'
			);

			$table->addColumn(
				'id',
				'integer',
				[
					'autoincrement' => true,
					'notnull' => true
				]
			);

			$table->addColumn(
				'extension_id',
				'string',
				[
					'length' => 64,
					'notnull' => true
				]
			);

			$table->addColumn(
				'config_key',
				'string',
				[
					'length' => 128,
					'notnull' => true
				]
			);

			$table->addColumn(
				'config_value',
				'text',
				[
					'notnull' => false
				]
			);

			$table->setPrimaryKey(
				['id']
			);

			$table->addIndex(
				['extension_id'],
				'kin_ext_cfg_idx'
			);

			$table->addUniqueIndex(
				[
					'extension_id',
					'config_key'
				],
				'kin_ext_cfg_unique'
			);
		}

		return $schema;
	}
}
