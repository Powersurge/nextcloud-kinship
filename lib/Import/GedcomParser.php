<?php

declare(strict_types=1);

namespace OCA\Kinship\Import;

class GedcomParser {
	public function parse(
		string $content,
	): array {

		$lines
			= preg_split(
				"/\r\n|\n|\r/",
				$content
			);

		$records = [];

		foreach ($lines as $line) {

			if (trim($line) === '') {
				continue;
			}

			preg_match(
				'/^(\d+)\s+(@[^@]+@)?\s*(.+)$/',
				$line,
				$matches
			);

			$records[] = [
				'level'
					=> (int)$matches[1],

				'xref'
					=> $matches[2] ?? null,

				'tag'
					=> $matches[3] ?? null
			];

		}

		return $records;
	}
}
