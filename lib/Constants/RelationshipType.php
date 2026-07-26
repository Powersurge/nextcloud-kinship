<?php

declare(strict_types=1);

namespace OCA\Kinship\Constants;

/**
 * Persisted relationship types and the canonicalization rules that keep the
 * relationship table free of contradictory / duplicate rows.
 *
 * Only the canonical set below is ever stored. Inverse "child-side" labels
 * (child, adopted_child, ...) and reversed symmetric relations are accepted on
 * input but normalized to a single canonical row. Every other kind of kinship
 * (siblings, grandparents, cousins, in-laws, step-relatives, aunts, uncles,
 * nieces, nephews, ...) is computed from the graph and never stored.
 *
 * Directed (parent-side) canonical rows mean: person_a is the <type> of
 * person_b (person_a is the parent/guardian, person_b is the child/ward).
 * Symmetric rows are stored with the lower person id as person_a.
 */
final class RelationshipType {
	// Canonical parent-side types (person_a = senior, person_b = junior).
	public const PARENT = 'parent';
	public const BIOLOGICAL_PARENT = 'biological_parent';
	public const ADOPTIVE_PARENT = 'adoptive_parent';
	public const FOSTER_PARENT = 'foster_parent';
	public const GUARDIAN = 'guardian';

	// Canonical symmetric types (order-independent).
	public const SPOUSE = 'spouse';
	public const FORMER_SPOUSE = 'former_spouse';
	public const PARTNER = 'partner';

	// Inverse (child-side) input labels, normalized to their parent-side type.
	public const CHILD = 'child';
	public const BIOLOGICAL_CHILD = 'biological_child';
	public const ADOPTED_CHILD = 'adopted_child';
	public const FOSTER_CHILD = 'foster_child';
	public const WARD = 'ward';

	/** Canonical types actually stored in the database. */
	public const CANONICAL = [
		self::PARENT,
		self::BIOLOGICAL_PARENT,
		self::ADOPTIVE_PARENT,
		self::FOSTER_PARENT,
		self::GUARDIAN,
		self::SPOUSE,
		self::FORMER_SPOUSE,
		self::PARTNER,
	];

	/** Symmetric canonical types (person_a / person_b interchangeable). */
	public const SYMMETRIC = [
		self::SPOUSE,
		self::FORMER_SPOUSE,
		self::PARTNER,
	];

	/**
	 * Parenthood types that form household families (person_a is the parent,
	 * person_b the child). Excludes GUARDIAN, which is a legal rather than a
	 * household relationship and is surfaced separately in the graph.
	 */
	public const PARENTHOOD = [
		self::PARENT,
		self::BIOLOGICAL_PARENT,
		self::ADOPTIVE_PARENT,
		self::FOSTER_PARENT,
	];

	/** child-side input label => canonical parent-side type. */
	public const CHILD_TO_PARENT = [
		self::CHILD => self::PARENT,
		self::BIOLOGICAL_CHILD => self::BIOLOGICAL_PARENT,
		self::ADOPTED_CHILD => self::ADOPTIVE_PARENT,
		self::FOSTER_CHILD => self::FOSTER_PARENT,
		self::WARD => self::GUARDIAN,
	];

	/** Every accepted input label (canonical + inverse). */
	public const ACCEPTED_INPUT = [
		self::PARENT,
		self::BIOLOGICAL_PARENT,
		self::ADOPTIVE_PARENT,
		self::FOSTER_PARENT,
		self::GUARDIAN,
		self::SPOUSE,
		self::FORMER_SPOUSE,
		self::PARTNER,
		self::CHILD,
		self::BIOLOGICAL_CHILD,
		self::ADOPTED_CHILD,
		self::FOSTER_CHILD,
		self::WARD,
	];

	public static function isAcceptedInput(string $type): bool {
		return in_array($type, self::ACCEPTED_INPUT, true);
	}

	public static function isSymmetric(string $type): bool {
		return in_array($type, self::SYMMETRIC, true);
	}

	public static function isParenthood(string $type): bool {
		return in_array($type, self::PARENTHOOD, true);
	}

	public static function isParentType(string $type): bool {
		return in_array($type, [
			self::PARENT,
			self::BIOLOGICAL_PARENT,
			self::ADOPTIVE_PARENT,
			self::FOSTER_PARENT,
			self::GUARDIAN,
		], true);
	}

	/**
	 * Normalize an input (person_a, person_b, type) triple into its canonical
	 * stored form.
	 *
	 * - child-side labels are swapped and mapped to their parent-side type
	 *   (input "A is child of B" => stored "B parent A").
	 * - symmetric relations are ordered so person_a is the lower id.
	 *
	 * @return array{personA: int, personB: int, type: string}
	 */
	public static function canonicalize(int $personA, int $personB, string $type): array {
		if (isset(self::CHILD_TO_PARENT[$type])) {
			// A is <child> of B  =>  B is <parent> of A.
			return [
				'personA' => $personB,
				'personB' => $personA,
				'type' => self::CHILD_TO_PARENT[$type],
			];
		}

		if (self::isSymmetric($type)) {
			$low = min($personA, $personB);
			$high = max($personA, $personB);

			return [
				'personA' => $low,
				'personB' => $high,
				'type' => $type,
			];
		}

		// Already canonical parent-side.
		return [
			'personA' => $personA,
			'personB' => $personB,
			'type' => $type,
		];
	}

	/**
	 * The reciprocal label of a stored canonical type, as seen from person_b
	 * looking back at person_a. Used when presenting derived relationships.
	 */
	public static function inverseLabel(string $canonicalType): string {
		$map = [
			self::PARENT => self::CHILD,
			self::BIOLOGICAL_PARENT => self::BIOLOGICAL_CHILD,
			self::ADOPTIVE_PARENT => self::ADOPTED_CHILD,
			self::FOSTER_PARENT => self::FOSTER_CHILD,
			self::GUARDIAN => self::WARD,
		];

		return $map[$canonicalType] ?? $canonicalType;
	}
}
