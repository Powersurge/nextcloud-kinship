<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_media - references linking a Nextcloud file to any genealogy object.
 * v0.1 records the reference; the full attach/permission workflow is v0.2.
 */
class Version0014Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		\Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_media')) {
			$table = $schema->createTable('kinship_media');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('album_id', 'integer', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('file_id', 'integer', ['notnull' => true, 'unsigned' => true]);
			$table->addColumn('subject_type', 'string', ['length' => 16, 'notnull' => true]);
			$table->addColumn('subject_id', 'integer', ['notnull' => true, 'unsigned' => true]);
			$table->addColumn('caption', 'string', ['length' => 255, 'notnull' => false]);
			$table->addColumn('is_primary', 'boolean', ['notnull' => false, 'default' => false]);
			$table->addColumn('sort_order', 'integer', ['notnull' => true, 'default' => 0]);
			$table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_by', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['subject_type', 'subject_id'], 'kinship_media_subject_idx');
			$table->addIndex(['file_id'], 'kinship_media_file_idx');
			$table->addIndex(['album_id'], 'kinship_media_album_idx');
		}

		return $schema;
	}
}
