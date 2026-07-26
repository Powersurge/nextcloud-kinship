<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_citations - links a source to any genealogy object with a locator
 * and optional transcription. confidence is nullable in v0.1 (evidence
 * weighting is a v0.3 concern).
 */
class Version0012Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		\Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_citations')) {
			$table = $schema->createTable('kinship_citations');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('source_id', 'integer', ['notnull' => true, 'unsigned' => true]);
			$table->addColumn('subject_type', 'string', ['length' => 16, 'notnull' => true]);
			$table->addColumn('subject_id', 'integer', ['notnull' => true, 'unsigned' => true]);
			$table->addColumn('detail', 'string', ['length' => 255, 'notnull' => false]);
			$table->addColumn('transcription', 'text', ['notnull' => false]);
			$table->addColumn('confidence', 'string', ['length' => 16, 'notnull' => false]);
			$table->addColumn('accessed_date', 'string', ['length' => 10, 'notnull' => false]);
			$table->addColumn('created_by', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('updated_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['source_id'], 'kinship_cit_source_idx');
			$table->addIndex(['subject_type', 'subject_id'], 'kinship_cit_subject_idx');
		}

		return $schema;
	}
}
