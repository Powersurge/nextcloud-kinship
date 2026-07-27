<?php

declare(strict_types=1);

namespace OCA\Kinship\Import;

use Gedcom\Gedcom;
use Gedcom\Record\Fam;
use Gedcom\Record\Indi;
use Gedcom\Record\Indi\Name;
use OCA\Kinship\Constants\LivingStatus;

/**
 * Maps a parsed {@see Gedcom} object into Kinship-ready import structures.
 */
class GedcomExtractor {
	/**
	 * @return array{
	 *   people: array<string, array<string, mixed>>,
	 *   families: array<string, array<string, mixed>>
	 * }
	 */
	public function extract(Gedcom $gedcom): array {
		$people = [];

		foreach ($gedcom->getIndi() as $indi) {
			$people[$indi->getId()] = $this->extractPerson($indi);
		}

		$families = [];
		foreach ($gedcom->getFam() as $fam) {
			$families[$fam->getId()] = $this->extractFamily($fam);
		}

		return [
			'people' => $people,
			'families' => $families,
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	private function extractPerson(Indi $indi): array {
		$name = $this->pickPrimaryName($indi);
		$birthEvent = $this->firstIndiEvent($indi, 'BIRT');
		$deathEvent = $this->firstIndiEvent($indi, 'DEAT');
		$birthDate = GedcomDateParser::fromIndiEvent($birthEvent)
			?? GedcomDateParser::fromBirthOrDeath($indi->getBirt());
		$deathDate = GedcomDateParser::fromIndiEvent($deathEvent)
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
	 * @return array<string, mixed>
	 */
	private function extractFamily(Fam $fam): array {
		$children = $fam->getChil();
		if (!is_array($children)) {
			$children = $children !== null && $children !== '' ? [$children] : [];
		}

		$marriageDate = null;
		$marr = $fam->getEven('MARR');
		if ($marr !== null) {
			$date = $marr->getDate();
			if (is_string($date) && trim($date) !== '') {
				$marriageDate = GedcomDateParser::fromDateString($date);
			}
		}

		$divorced = $fam->getEven('DIV') !== null;

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
		];
	}

	private function normalizeRef(mixed $ref): ?string {
		if (!is_string($ref)) {
			return null;
		}

		$ref = trim($ref);

		return $ref !== '' ? $ref : null;
	}
}
