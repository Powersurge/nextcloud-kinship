<?php

declare(strict_types=1);

namespace OCA\Kinship\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0006Date20260718000000 extends SimpleMigrationStep {

	public function changeSchema(
		IOutput $output,
		Closure $schemaClosure,
		array $options,
	): ?ISchemaWrapper {

		$schema = $schemaClosure();

		$table
			= $schema->getTable(
				'kinship_events'
			);

		if (
			!$table->hasColumn(
				'person_id'
			)
		) {

			$table->addColumn(
				'person_id',
				'integer',
				[
					'notnull'
						=> false
				]
			);

		}

		if (
			!$table->hasColumn(
				'family_id'
			)
		) {

			$table->addColumn(
				'family_id',
				'integer',
				[
					'notnull'
						=> false
				]
			);

		}

		return $schema;
	}

}
