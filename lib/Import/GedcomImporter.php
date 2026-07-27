<?php

declare(strict_types=1);

namespace OCA\Kinship\Import;

use Gedcom\Parser;
use OCA\Kinship\Constants\RelationshipType;
use OCA\Kinship\Db\DateValue;
use OCA\Kinship\Service\FamilyMembershipService;

/**
 * Orchestrates GEDCOM import: parse, extract, persist people and relationships.
 */
class GedcomImporter {
	public function __construct(
		private GedcomExtractor $extractor,
		private GedcomPersonImporter $personImporter,
		private GedcomRelationshipImporter $relationshipImporter,
		private FamilyMembershipService $familyMembership,
	) {
	}

	/**
	 * @return array{imported: int, relationships: int}
	 */
	public function import(string $content): array {
		$this->ensureGedcomLibraryLoaded();

		if (trim($content) === '') {
			throw new \InvalidArgumentException('GEDCOM content is empty');
		}

		$tempFile = tempnam(sys_get_temp_dir(), 'kinship-gedcom-');
		if ($tempFile === false) {
			throw new \RuntimeException('Could not create temporary file for GEDCOM import');
		}

		try {
			if (file_put_contents($tempFile, $content) === false) {
				throw new \RuntimeException('Could not write GEDCOM content to temporary file');
			}

			$parser = new Parser();
			$gedcom = $parser->parse($tempFile);

			if ($gedcom === null) {
				throw new \RuntimeException('Failed to parse GEDCOM file');
			}

			$extracted = $this->extractor->extract($gedcom);
			$idMap = $this->personImporter->import($extracted['people']);
			$relationshipCount = $this->importFamilies($extracted['families'], $idMap);

			$this->familyMembership->rebuildAll();

			return [
				'imported' => count($idMap),
				'relationships' => $relationshipCount,
			];
		} finally {
			if (is_file($tempFile)) {
				unlink($tempFile);
			}
		}
	}

	/**
	 * @param array<string, array<string, mixed>> $families
	 * @param array<string, int> $idMap
	 */
	private function importFamilies(array $families, array $idMap): int {
		$count = 0;

		foreach ($families as $family) {
			$husbandId = isset($family['husband']) ? ($idMap[$family['husband']] ?? null) : null;
			$wifeId = isset($family['wife']) ? ($idMap[$family['wife']] ?? null) : null;
			$marriageDate = !empty($family['marriageDate'])
				? DateValue::fromArray($family['marriageDate'])
				: null;

			if ($husbandId !== null && $wifeId !== null) {
				$type = !empty($family['divorced'])
					? RelationshipType::FORMER_SPOUSE
					: RelationshipType::SPOUSE;

				if ($this->relationshipImporter->create($husbandId, $wifeId, $type, $marriageDate) !== null) {
					$count++;
				}
			}

			$parents = array_values(array_filter([$husbandId, $wifeId]));
			$children = $family['children'] ?? [];

			if (!is_array($children)) {
				continue;
			}

			foreach ($children as $childRef) {
				$childId = $idMap[$childRef] ?? null;
				if ($childId === null) {
					continue;
				}

				foreach ($parents as $parentId) {
					if ($this->relationshipImporter->create(
						$parentId,
						$childId,
						RelationshipType::BIOLOGICAL_PARENT,
					) !== null) {
						$count++;
					}
				}
			}
		}

		return $count;
	}

	private function ensureGedcomLibraryLoaded(): void {
		if (class_exists(Parser::class, false)) {
			return;
		}

		$autoload = __DIR__ . '/../../vendor/autoload.php';
		if (is_file($autoload)) {
			require_once $autoload;
		}
	}
}
