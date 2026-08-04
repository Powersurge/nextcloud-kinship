<?php

declare(strict_types=1);

namespace OCA\Kinship\Import;

/**
 * Indexes raw DATE values from GEDCOM text by record xref and event tag.
 *
 * liberu-genealogy/php-gedcom's Date parser collapses many values to bare
 * years (losing ABT/BEF/BET) or short ISO strings, so the importer prefers
 * these original strings when available.
 */
final class GedcomRawDateIndex {
	/**
	 * @return array<string, array<string, string>>
	 */
	public static function build(string $content): array {
		$index = [];
		$currentId = null;
		$currentEvent = null;

		$lines = preg_split('/\r\n|\n|\r/', $content) ?: [];
		foreach ($lines as $line) {
			if (!preg_match('/^(\d+)\s+(?:@([^@]+)@\s+)?(\S+)(?:\s+(.*))?$/', $line, $matches)) {
				continue;
			}

			$level = (int)$matches[1];
			$xref = $matches[2] ?? '';
			$tag = strtoupper($matches[3]);
			$value = isset($matches[4]) ? trim($matches[4]) : '';

			if ($level === 0) {
				$currentId = $xref !== '' ? $xref : null;
				$currentEvent = null;
				continue;
			}

			if ($currentId === null) {
				continue;
			}

			if ($level === 1) {
				$currentEvent = in_array($tag, ['BIRT', 'DEAT', 'MARR', 'CHR', 'BURI', 'DIV'], true)
					? $tag
					: null;
				continue;
			}

			if ($level === 2 && $tag === 'DATE' && $currentEvent !== null && $value !== '') {
				$index[$currentId][$currentEvent] = $value;
			}
		}

		return $index;
	}
}
