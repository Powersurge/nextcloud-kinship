<?php

declare(strict_types=1);

namespace OCA\Kinship\Import;

use OCA\Kinship\Constants\CitationConfidence;
use OCA\Kinship\Constants\SubjectType;
use OCA\Kinship\Exception\ValidationException;
use OCA\Kinship\Http\Requests\CitationRequest;
use OCA\Kinship\Http\Requests\SourceRequest;
use OCA\Kinship\Service\CitationService;
use OCA\Kinship\Service\SourceService;
use OCA\Kinship\Validation\RequestValidator;

/**
 * Persists extracted GEDCOM SOUR records and SourRef citations.
 */
class GedcomSourceImporter {
	public function __construct(
		private SourceService $sourceService,
		private CitationService $citationService,
		private RequestValidator $requestValidator,
	) {
	}

	/**
	 * @param array<string, array<string, mixed>> $sources keyed by GEDCOM xref
	 * @return array<string, int> gedcom xref => kinship source id
	 */
	public function importSources(array $sources): array {
		/** @var array<string, int> $map */
		$map = [];

		foreach ($sources as $gedcomId => $data) {
			$title = trim((string)($data['title'] ?? ''));
			if ($title === '') {
				$title = 'Source ' . $gedcomId;
			}

			try {
				$validated = $this->requestValidator->validate([
					'title' => $title,
					'author' => $data['author'] ?? null,
					'publication' => $data['publication'] ?? null,
					'repository' => $data['repository'] ?? null,
					'notes' => $data['notes'] ?? null,
					'sourceType' => null,
					'url' => null,
				], SourceRequest::class);
			} catch (ValidationException) {
				continue;
			}

			$source = $this->sourceService->create($validated);
			$map[$gedcomId] = $source->getId();
		}

		return $map;
	}

	/**
	 * @param list<array{sourceRef: string, detail: ?string, transcription: ?string, confidence: ?string}> $citations
	 * @param array<string, int> $sourceMap
	 */
	public function importCitations(
		array $citations,
		array $sourceMap,
		string $subjectType,
		int $subjectId,
	): int {
		if (!SubjectType::isCitable($subjectType) || $subjectId < 1) {
			return 0;
		}

		$count = 0;

		foreach ($citations as $citation) {
			$sourceRef = $citation['sourceRef'] ?? null;
			if (!is_string($sourceRef) || $sourceRef === '') {
				continue;
			}

			$sourceId = $sourceMap[$sourceRef] ?? null;
			if ($sourceId === null) {
				continue;
			}

			$confidence = $citation['confidence'] ?? null;
			if (is_string($confidence) && !CitationConfidence::isValid($confidence)) {
				$confidence = CitationConfidence::fromQuay($confidence) ?? null;
			}

			try {
				$validated = $this->requestValidator->validate([
					'sourceId' => $sourceId,
					'subjectType' => $subjectType,
					'subjectId' => $subjectId,
					'detail' => $citation['detail'] ?? null,
					'transcription' => $citation['transcription'] ?? null,
					'confidence' => $confidence,
					'accessedDate' => null,
				], CitationRequest::class);
			} catch (ValidationException) {
				continue;
			}

			$this->citationService->create($validated);
			$count++;
		}

		return $count;
	}
}
