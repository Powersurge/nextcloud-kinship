<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_albums + album_id/sort_order on kinship_media.
 */
class Version0017Date20260804000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_albums')) {
			$table = $schema->createTable('kinship_albums');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('title', 'string', ['length' => 255, 'notnull' => true]);
			$table->addColumn('description', 'text', ['notnull' => false]);
			$table->addColumn('owner_type', 'string', ['length' => 16, 'notnull' => true]);
			$table->addColumn('owner_id', 'integer', ['notnull' => true, 'unsigned' => true]);
			$table->addColumn('cover_media_id', 'integer', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_by', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('updated_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['owner_type', 'owner_id'], 'kinship_albums_owner_idx');
		}

		return $schema;
	}
}
