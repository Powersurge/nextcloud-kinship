<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_sources - bibliographic sources. repository is free text in v0.1;
 * a dedicated Repositories entity arrives in v0.3.
 */
class Version0011Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		\Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_sources')) {
			$table = $schema->createTable('kinship_sources');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('title', 'string', ['length' => 255, 'notnull' => true]);
			$table->addColumn('author', 'string', ['length' => 255, 'notnull' => false]);
			$table->addColumn('publication', 'string', ['length' => 255, 'notnull' => false]);
			$table->addColumn('repository', 'string', ['length' => 255, 'notnull' => false]);
			$table->addColumn('source_type', 'string', ['length' => 32, 'notnull' => false]);
			$table->addColumn('url', 'string', ['length' => 512, 'notnull' => false]);
			$table->addColumn('notes', 'text', ['notnull' => false]);
			$table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_by', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('updated_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['owner_uid'], 'kinship_source_owner_idx');
			$table->addIndex(['title'], 'kinship_source_title_idx');
		}

		return $schema;
	}
}
