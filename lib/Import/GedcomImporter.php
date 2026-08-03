<?php

declare(strict_types=1);

namespace OCA\Kinship\Import;

use Gedcom\Parser;
use OCA\Kinship\Constants\RelationshipType;
use OCA\Kinship\Constants\SubjectType;
use OCA\Kinship\Db\DateValue;
use OCA\Kinship\Service\FamilyMembershipService;

/**
 * Orchestrates GEDCOM import: parse, extract, persist people, relationships, sources.
 */
class GedcomImporter {
	public function __construct(
		private GedcomExtractor $extractor,
		private GedcomPersonImporter $personImporter,
		private GedcomRelationshipImporter $relationshipImporter,
		private GedcomSourceImporter $sourceImporter,
		private FamilyMembershipService $familyMembership,
	) {
	}

	/**
	 * @return array{imported: int, relationships: int, sources: int, citations: int}
	 */
	public function import(string $content): array {
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
			$sourceMap = $this->sourceImporter->importSources($extracted['sources'] ?? []);
			$imported = $this->personImporter->import($extracted['people']);
			$idMap = $imported['people'];
			$eventsMap = $imported['events'];
			$familyImport = $this->importFamilies($extracted['families'], $idMap);

			$citationCount = $this->importPersonCitations(
				$extracted['people'],
				$idMap,
				$eventsMap,
				$sourceMap,
			);
			$citationCount += $this->importFamilyCitations(
				$extracted['families'],
				$familyImport['partnershipIds'],
				$sourceMap,
			);

			$this->familyMembership->rebuildAll();

			return [
				'imported' => count($idMap),
				'relationships' => $familyImport['count'],
				'sources' => count($sourceMap),
				'citations' => $citationCount,
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
	 * @return array{count: int, partnershipIds: array<string, int>}
	 */
	private function importFamilies(array $families, array $idMap): array {
		$count = 0;
		/** @var array<string, int> $partnershipIds */
		$partnershipIds = [];

		foreach ($families as $gedcomFamilyId => $family) {
			$husbandId = isset($family['husband']) ? ($idMap[$family['husband']] ?? null) : null;
			$wifeId = isset($family['wife']) ? ($idMap[$family['wife']] ?? null) : null;
			$marriageDate = !empty($family['marriageDate'])
				? DateValue::fromArray($family['marriageDate'])
				: null;

			if ($husbandId !== null && $wifeId !== null) {
				$type = !empty($family['divorced'])
					? RelationshipType::DIVORCED
					: RelationshipType::MARRIED;

				$relationship = $this->relationshipImporter->create($husbandId, $wifeId, $type, $marriageDate);
				if ($relationship !== null) {
					$count++;
					$partnershipIds[(string)$gedcomFamilyId] = $relationship->getId();
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

		return [
			'count' => $count,
			'partnershipIds' => $partnershipIds,
		];
	}

	/**
	 * @param array<string, array<string, mixed>> $people
	 * @param array<string, int> $idMap
	 * @param array<string, array{birth: ?int, death: ?int}> $eventsMap
	 * @param array<string, int> $sourceMap
	 */
	private function importPersonCitations(
		array $people,
		array $idMap,
		array $eventsMap,
		array $sourceMap,
	): int {
		$count = 0;

		foreach ($people as $gedcomId => $data) {
			$personId = $idMap[$gedcomId] ?? null;
			if ($personId === null) {
				continue;
			}

			$count += $this->sourceImporter->importCitations(
				is_array($data['citations'] ?? null) ? $data['citations'] : [],
				$sourceMap,
				SubjectType::PERSON,
				$personId,
			);

			$birthEventId = $eventsMap[$gedcomId]['birth'] ?? null;
			if ($birthEventId !== null) {
				$count += $this->sourceImporter->importCitations(
					is_array($data['birthCitations'] ?? null) ? $data['birthCitations'] : [],
					$sourceMap,
					SubjectType::EVENT,
					$birthEventId,
				);
			}

			$deathEventId = $eventsMap[$gedcomId]['death'] ?? null;
			if ($deathEventId !== null) {
				$count += $this->sourceImporter->importCitations(
					is_array($data['deathCitations'] ?? null) ? $data['deathCitations'] : [],
					$sourceMap,
					SubjectType::EVENT,
					$deathEventId,
				);
			}
		}

		return $count;
	}

	/**
	 * @param array<string, array<string, mixed>> $families
	 * @param array<string, int> $partnershipIds
	 * @param array<string, int> $sourceMap
	 */
	private function importFamilyCitations(
		array $families,
		array $partnershipIds,
		array $sourceMap,
	): int {
		$count = 0;

		foreach ($families as $gedcomFamilyId => $family) {
			$relationshipId = $partnershipIds[(string)$gedcomFamilyId] ?? null;
			if ($relationshipId === null) {
				continue;
			}

			$count += $this->sourceImporter->importCitations(
				is_array($family['citations'] ?? null) ? $family['citations'] : [],
				$sourceMap,
				SubjectType::RELATIONSHIP,
				$relationshipId,
			);
		}

		return $count;
	}
}
