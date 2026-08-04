<?php

declare(strict_types=1);

namespace OCA\Kinship\Import;

use Gedcom\Gedcom;
use Gedcom\Record\Fam;
use Gedcom\Record\Indi;
use Gedcom\Record\Indi\Name;
use Gedcom\Record\Sour;
use Gedcom\Record\SourRef;
use OCA\Kinship\Constants\CitationConfidence;
use OCA\Kinship\Constants\LivingStatus;

/**
 * Maps a parsed {@see Gedcom} object into Kinship-ready import structures.
 */
class GedcomExtractor {
	/**
	 * @param array<string, array<string, string>> $rawDates
	 * @return array{
	 *   people: array<string, array<string, mixed>>,
	 *   families: array<string, array<string, mixed>>,
	 *   sources: array<string, array<string, mixed>>
	 * }
	 */
	public function extract(Gedcom $gedcom, array $rawDates = []): array {
		$sources = [];
		foreach ($gedcom->getSour() as $sour) {
			if (!$sour instanceof Sour) {
				continue;
			}
			$id = $this->normalizeRef($sour->getSour() ?: $sour->getId());
			if ($id === null) {
				continue;
			}
			$sources[$id] = $this->extractSource($sour, $id);
		}

		$people = [];
		foreach ($gedcom->getIndi() as $indi) {
			$people[$indi->getId()] = $this->extractPerson($indi, $rawDates);
		}

		$families = [];
		foreach ($gedcom->getFam() as $fam) {
			$families[$fam->getId()] = $this->extractFamily($fam, $rawDates);
		}

		return [
			'people' => $people,
			'families' => $families,
			'sources' => $sources,
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	private function extractSource(Sour $sour, string $id): array {
		$repo = $sour->getRepo();
		$repository = null;
		if (is_object($repo) && method_exists($repo, 'getRepo')) {
			$repository = $this->normalizeRef($repo->getRepo());
		}

		$notes = $sour->getText();
		if (!is_string($notes) || trim($notes) === '') {
			$notes = null;
		}

		$title = is_string($sour->getTitl()) ? trim($sour->getTitl()) : '';
		if ($title === '') {
			$abbr = is_string($sour->getAbbr()) ? trim($sour->getAbbr()) : '';
			$title = $abbr !== '' ? $abbr : 'Source ' . $id;
		}

		return [
			'gedcomId' => $id,
			'title' => $title,
			'author' => $this->nullableString($sour->getAuth()),
			'publication' => $this->nullableString($sour->getPubl()),
			'repository' => $repository,
			'notes' => $notes,
		];
	}

	/**
	 * @param array<string, array<string, string>> $rawDates
	 * @return array<string, mixed>
	 */
	private function extractPerson(Indi $indi, array $rawDates = []): array {
		$name = $this->pickPrimaryName($indi);
		$birthEvent = $this->firstIndiEvent($indi, 'BIRT');
		$deathEvent = $this->firstIndiEvent($indi, 'DEAT');
		$indiId = (string)$indi->getId();
		$birthDate = GedcomDateParser::fromDateString($rawDates[$indiId]['BIRT'] ?? null)
			?? GedcomDateParser::fromIndiEvent($birthEvent)
			?? GedcomDateParser::fromBirthOrDeath($indi->getBirt());
		$deathDate = GedcomDateParser::fromDateString($rawDates[$indiId]['DEAT'] ?? null)
			?? GedcomDateParser::fromIndiEvent($deathEvent)
			?? GedcomDateParser::fromBirthOrDeath($indi->getDeat());

		return [
			'gedcomId' => $indi->getId(),
			'firstName' => $name['firstName'],
			'middleName' => $name['middleName'],
			'lastName' => $name['lastName'],
			'preferredName' => $name['preferredName'],
			'suffix' => $name['suffix'],
			'gender' => $this->mapGender($indi->getSex()),
			'livingStatus' => $deathDate !== null || $deathEvent !== null || $indi->getDeat() !== null
				? LivingStatus::DECEASED
				: LivingStatus::UNKNOWN,
			'birthDate' => $birthDate,
			'deathDate' => $deathDate,
			'citations' => $this->extractSourRefs($indi->getSour()),
			'birthCitations' => $birthEvent !== null ? $this->extractSourRefs($birthEvent->getSour()) : [],
			'deathCitations' => $deathEvent !== null ? $this->extractSourRefs($deathEvent->getSour()) : [],
		];
	}

	private function firstIndiEvent(Indi $indi, string $type): ?\Gedcom\Record\Indi\Even {
		$events = $indi->getEven($type);

		if (!is_array($events) || $events === []) {
			return null;
		}

		$event = $events[0];

		return $event instanceof \Gedcom\Record\Indi\Even ? $event : null;
	}

	/**
	 * @return array{
	 *   firstName: string,
	 *   middleName: ?string,
	 *   lastName: string,
	 *   preferredName: ?string,
	 *   suffix: ?string
	 * }
	 */
	private function pickPrimaryName(Indi $indi): array {
		$names = $indi->getName();
		if ($names === []) {
			return [
				'firstName' => 'Unknown',
				'middleName' => null,
				'lastName' => '',
				'preferredName' => null,
				'suffix' => null,
			];
		}

		return $this->parseName($names[0]);
	}

	/**
	 * @return array{
	 *   firstName: string,
	 *   middleName: ?string,
	 *   lastName: string,
	 *   preferredName: ?string,
	 *   suffix: ?string
	 * }
	 */
	private function parseName(Name $name): array {
		$given = $this->namePart($name, 'Givn');
		$surname = $this->namePart($name, 'Surn');
		$suffix = $this->namePart($name, 'Nsfx') ?: null;
		$preferred = $this->namePart($name, 'Nick') ?: null;

		if ($given === '' && $surname === '') {
			$full = $this->namePart($name, 'Name');
			if (preg_match('/^([^\/]*)\/([^\/]*)\/(.*)$/', $full, $matches) === 1) {
				$given = trim($matches[1]);
				$surname = trim($matches[2]);
				$trailing = trim($matches[3]);
				if ($suffix === null && $trailing !== '') {
					$suffix = $trailing;
				}
			} elseif ($full !== '') {
				$given = $full;
			}
		}

		$firstName = '';
		$middleName = null;
		if ($given !== '') {
			$givenParts = preg_split('/\s+/', $given) ?: [];
			$firstName = array_shift($givenParts) ?? '';
			if ($givenParts !== []) {
				$middleName = implode(' ', $givenParts);
			}
		}

		if ($firstName === '') {
			$firstName = 'Unknown';
		}

		return [
			'firstName' => $firstName,
			'middleName' => $middleName,
			'lastName' => $surname,
			'preferredName' => $preferred,
			'suffix' => $suffix,
		];
	}

	private function namePart(Name $name, string $part): string {
		$method = 'get' . $part;

		try {
			$value = $name->$method();
		} catch (\Throwable) {
			return '';
		}

		return is_string($value) ? trim($value) : '';
	}

	private function mapGender(string $sex): ?string {
		return match (strtoupper(trim($sex))) {
			'M' => 'male',
			'F' => 'female',
			default => null,
		};
	}

	/**
	 * @param array<string, array<string, string>> $rawDates
	 * @return array<string, mixed>
	 */
	private function extractFamily(Fam $fam, array $rawDates = []): array {
		$children = $fam->getChil();
		if (!is_array($children)) {
			$children = $children !== null && $children !== '' ? [$children] : [];
		}

		$famId = (string)$fam->getId();
		$marriageDate = GedcomDateParser::fromDateString($rawDates[$famId]['MARR'] ?? null);
		$marr = $fam->getEven('MARR');
		if (is_array($marr)) {
			$marr = $marr[0] ?? null;
		}
		if ($marriageDate === null && $marr !== null && is_object($marr) && method_exists($marr, 'getDate')) {
			$marriageDate = GedcomDateParser::fromDateString($marr->getDate());
		}

		$div = $fam->getEven('DIV');
		$divorced = $div !== null && $div !== [];
		try {
			$familySours = $fam->getSour();
		} catch (\Throwable) {
			$familySours = [];
		}

		return [
			'gedcomId' => $fam->getId(),
			'husband' => $this->normalizeRef($fam->getHusb()),
			'wife' => $this->normalizeRef($fam->getWife()),
			'children' => array_values(array_filter(array_map(
				fn ($child) => $this->normalizeRef($child),
				$children,
			))),
			'marriageDate' => $marriageDate,
			'divorced' => $divorced,
			'citations' => $this->extractSourRefs($familySours),
		];
	}

	/**
	 * @param mixed $refs
	 * @return list<array{sourceRef: string, detail: ?string, transcription: ?string, confidence: ?string}>
	 */
	private function extractSourRefs(mixed $refs): array {
		if (!is_array($refs)) {
			return [];
		}

		$citations = [];
		foreach ($refs as $ref) {
			if (!$ref instanceof SourRef) {
				continue;
			}

			$sourceRef = $this->normalizeRef($ref->getSour());
			if ($sourceRef === null) {
				continue;
			}

			$text = $this->nullableString($ref->getText());
			$data = $ref->getData();
			if ($text === null && is_object($data) && method_exists($data, 'getText')) {
				$text = $this->nullableString($data->getText());
			}

			$citations[] = [
				'sourceRef' => $sourceRef,
				'detail' => $this->nullableString($ref->getPage()),
				'transcription' => $text,
				'confidence' => CitationConfidence::fromQuay($ref->getQuay()),
			];
		}

		return $citations;
	}

	private function nullableString(mixed $value): ?string {
		if (!is_string($value)) {
			return null;
		}

		$value = trim($value);

		return $value !== '' ? $value : null;
	}

	private function normalizeRef(mixed $ref): ?string {
		if (!is_string($ref)) {
			return null;
		}

		$ref = trim($ref);
		$ref = trim($ref, '@');

		return $ref !== '' ? $ref : null;
	}
}
