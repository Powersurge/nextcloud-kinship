<?php

declare(strict_types=1);

namespace OCA\Kinship\Import;

class GedcomExtractor {

	public function extract(
		array $records,
	): array {

		$people = [];

		$families = [];

		foreach ($records as $record) {

			if (
				$record['tag'] === 'INDI'
			) {

				$people[
					$record['xref']
				] = [
					'id'
						=> $record['xref']
				];

			}

			if (
				$record['tag'] === 'FAM'
			) {

				$families[
					$record['xref']
				] = [
					'id'
						=> $record['xref']
				];

			}

			if ($record['tag'] === 'HUSB') {

				$families[
					$currentFamily
				]['husband']
					= $record['xref'];

			}

			if ($record['tag'] === 'WIFE') {

				$families[
					$currentFamily
				]['wife']
					= $record['xref'];

			}

			if ($record['tag'] === 'CHIL') {

				$families[
					$currentFamily
				]['children'][]
					= $record['xref'];

			}

		}

		return [
			'people'
				=> $people,

			'families'
				=> $families
		];

	}

}
