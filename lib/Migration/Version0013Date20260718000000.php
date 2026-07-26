<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * kinship_claims - proposed changes pending review.
 *
 * subject_id is null when the claim proposes creating a new record.
 * proposed_value holds a JSON payload. The full resolution workflow
 * (confidence, conflicting evidence) is a v0.3 concern.
 */
class Version0013Date20260718000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		\Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('kinship_claims')) {
			$table = $schema->createTable('kinship_claims');

			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('subject_type', 'string', ['length' => 16, 'notnull' => true]);
			$table->addColumn('subject_id', 'integer', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('field', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('proposed_value', 'text', ['notnull' => false]);
			$table->addColumn('operation', 'string', ['length' => 16, 'notnull' => true, 'default' => 'update']);
			$table->addColumn('status', 'string', ['length' => 16, 'notnull' => true, 'default' => 'pending']);
			$table->addColumn('confidence', 'string', ['length' => 16, 'notnull' => false]);
			$table->addColumn('rationale', 'text', ['notnull' => false]);
			$table->addColumn('source_id', 'integer', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('created_by', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('reviewed_by', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('reviewed_at', 'bigint', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('created_at', 'bigint', ['notnull' => false, 'unsigned' => true]);
			$table->addColumn('updated_at', 'bigint', ['notnull' => false, 'unsigned' => true]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['subject_type', 'subject_id'], 'kinship_claim_subject_idx');
			$table->addIndex(['status'], 'kinship_claim_status_idx');
			$table->addIndex(['created_by'], 'kinship_claim_creator_idx');
		}

		return $schema;
	}
}
