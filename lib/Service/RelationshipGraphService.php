<?php

declare(strict_types=1);

namespace OCA\Kinship\Service;

use OCA\Kinship\Constants\RelationshipType;
use OCA\Kinship\Db\Person;
use OCA\Kinship\Db\PersonMapper;
use OCA\Kinship\Db\RelationshipMapper;

/**
 * Computes derived kinship from the stored canonical relationship graph.
 *
 * Nothing here is persisted: only the ~dozen canonical types live in the
 * database, and every other relationship (siblings, grandparents, cousins,
 * in-laws, step-relatives, aunts, uncles, nieces, nephews, ...) is calculated
 * on read from the adjacency the canonical rows describe.
 */
class RelationshipGraphService {
	/** @var array<int, list<int>> childId => parent ids */
	private array $parents = [];
	/** @var array<int, list<int>> parentId => child ids */
	private array $children = [];
	/** @var array<int, list<int>> current spouses */
	private array $spouses = [];
	/** @var array<int, list<int>> former spouses */
	private array $formerSpouses = [];
	/** @var array<int, list<int>> partners */
	private array $partners = [];
	/** @var array<int, list<int>> wardId => guardian ids */
	private array $guardians = [];
	/** @var array<int, list<int>> guardianId => ward ids */
	private array $wards = [];

	private bool $loaded = false;

	public function __construct(
		private RelationshipMapper $relationshipMapper,
		private PersonMapper $personMapper,
	) {
	}

	/**
	 * Derived relationships for a person, each category hydrated to Person[].
	 *
	 * @return array<string, Person[]>
	 */
	public function getDerived(int $personId): array {
		$this->load();

		$categories = [
			'parents' => $this->at($this->parents, $personId),
			'children' => $this->at($this->children, $personId),
			'spouses' => $this->at($this->spouses, $personId),
			'formerSpouses' => $this->at($this->formerSpouses, $personId),
			'partners' => $this->at($this->partners, $personId),
			'guardians' => $this->at($this->guardians, $personId),
			'wards' => $this->at($this->wards, $personId),
			'siblings' => $this->siblingsOf($personId),
			'grandparents' => $this->grandparentsOf($personId),
			'grandchildren' => $this->grandchildrenOf($personId),
			'auntsUncles' => $this->auntsUnclesOf($personId),
			'niecesNephews' => $this->niecesNephewsOf($personId),
			'cousins' => $this->cousinsOf($personId),
			'parentsInLaw' => $this->parentsInLawOf($personId),
			'childrenInLaw' => $this->childrenInLawOf($personId),
			'siblingsInLaw' => $this->siblingsInLawOf($personId),
			'stepParents' => $this->stepParentsOf($personId),
			'stepChildren' => $this->stepChildrenOf($personId),
		];

		return $this->hydrate($categories);
	}

	private function load(): void {
		if ($this->loaded) {
			return;
		}

		foreach ($this->relationshipMapper->findAll() as $rel) {
			$a = $rel->getPersonA();
			$b = $rel->getPersonB();
			$type = $rel->getType();

			if (RelationshipType::isParenthood($type)) {
				$this->parents[$b][] = $a;
				$this->children[$a][] = $b;
			} elseif ($type === RelationshipType::SPOUSE) {
				$this->spouses[$a][] = $b;
				$this->spouses[$b][] = $a;
			} elseif ($type === RelationshipType::FORMER_SPOUSE) {
				$this->formerSpouses[$a][] = $b;
				$this->formerSpouses[$b][] = $a;
			} elseif ($type === RelationshipType::PARTNER) {
				$this->partners[$a][] = $b;
				$this->partners[$b][] = $a;
			} elseif ($type === RelationshipType::GUARDIAN) {
				$this->wards[$a][] = $b;
				$this->guardians[$b][] = $a;
			}
		}

		$this->loaded = true;
	}

	/**
	 * @param array<int, list<int>> $map
	 * @return list<int>
	 */
	private function at(array $map, int $id): array {
		return array_values(array_unique($map[$id] ?? []));
	}

	/**
	 * Everyone who shares at least one parent with the person, minus self.
	 *
	 * @return list<int>
	 */
	private function siblingsOf(int $id): array {
		$out = [];
		foreach ($this->at($this->parents, $id) as $parent) {
			foreach ($this->at($this->children, $parent) as $child) {
				if ($child !== $id) {
					$out[$child] = true;
				}
			}
		}

		return array_keys($out);
	}

	/**
	 * @return list<int>
	 */
	private function allPartnersOf(int $id): array {
		return array_values(array_unique(array_merge(
			$this->at($this->spouses, $id),
			$this->at($this->formerSpouses, $id),
			$this->at($this->partners, $id),
		)));
	}

	/**
	 * @return list<int>
	 */
	private function grandparentsOf(int $id): array {
		$out = [];
		foreach ($this->at($this->parents, $id) as $parent) {
			foreach ($this->at($this->parents, $parent) as $grandparent) {
				$out[$grandparent] = true;
			}
		}

		return array_keys($out);
	}

	/**
	 * @return list<int>
	 */
	private function grandchildrenOf(int $id): array {
		$out = [];
		foreach ($this->at($this->children, $id) as $child) {
			foreach ($this->at($this->children, $child) as $grandchild) {
				$out[$grandchild] = true;
			}
		}

		return array_keys($out);
	}

	/**
	 * Siblings of the person's parents.
	 *
	 * @return list<int>
	 */
	private function auntsUnclesOf(int $id): array {
		$out = [];
		foreach ($this->at($this->parents, $id) as $parent) {
			foreach ($this->siblingsOf($parent) as $sibling) {
				$out[$sibling] = true;
			}
		}

		return array_keys($out);
	}

	/**
	 * Children of the person's siblings.
	 *
	 * @return list<int>
	 */
	private function niecesNephewsOf(int $id): array {
		$out = [];
		foreach ($this->siblingsOf($id) as $sibling) {
			foreach ($this->at($this->children, $sibling) as $child) {
				$out[$child] = true;
			}
		}

		return array_keys($out);
	}

	/**
	 * Children of the person's aunts and uncles.
	 *
	 * @return list<int>
	 */
	private function cousinsOf(int $id): array {
		$out = [];
		foreach ($this->auntsUnclesOf($id) as $auntUncle) {
			foreach ($this->at($this->children, $auntUncle) as $child) {
				if ($child !== $id) {
					$out[$child] = true;
				}
			}
		}

		return array_keys($out);
	}

	/**
	 * Parents of the person's partners.
	 *
	 * @return list<int>
	 */
	private function parentsInLawOf(int $id): array {
		$out = [];
		foreach ($this->allPartnersOf($id) as $partner) {
			foreach ($this->at($this->parents, $partner) as $parent) {
				$out[$parent] = true;
			}
		}

		return array_keys($out);
	}

	/**
	 * Partners of the person's children.
	 *
	 * @return list<int>
	 */
	private function childrenInLawOf(int $id): array {
		$out = [];
		foreach ($this->at($this->children, $id) as $child) {
			foreach ($this->allPartnersOf($child) as $partner) {
				$out[$partner] = true;
			}
		}

		return array_keys($out);
	}

	/**
	 * Siblings of the person's partners, plus partners of the person's siblings.
	 *
	 * @return list<int>
	 */
	private function siblingsInLawOf(int $id): array {
		$out = [];
		foreach ($this->allPartnersOf($id) as $partner) {
			foreach ($this->siblingsOf($partner) as $sibling) {
				$out[$sibling] = true;
			}
		}
		foreach ($this->siblingsOf($id) as $sibling) {
			foreach ($this->allPartnersOf($sibling) as $partner) {
				$out[$partner] = true;
			}
		}
		unset($out[$id]);

		return array_keys($out);
	}

	/**
	 * Partners of the person's parents who are not themselves parents.
	 *
	 * @return list<int>
	 */
	private function stepParentsOf(int $id): array {
		$actualParents = array_flip($this->at($this->parents, $id));
		$out = [];
		foreach ($this->at($this->parents, $id) as $parent) {
			foreach ($this->allPartnersOf($parent) as $partner) {
				if (!isset($actualParents[$partner])) {
					$out[$partner] = true;
				}
			}
		}

		return array_keys($out);
	}

	/**
	 * Children of the person's partners who are not the person's own children.
	 *
	 * @return list<int>
	 */
	private function stepChildrenOf(int $id): array {
		$ownChildren = array_flip($this->at($this->children, $id));
		$out = [];
		foreach ($this->allPartnersOf($id) as $partner) {
			foreach ($this->at($this->children, $partner) as $child) {
				if (!isset($ownChildren[$child])) {
					$out[$child] = true;
				}
			}
		}

		return array_keys($out);
	}

	/**
	 * Replace id lists with Person entities, loading each person once.
	 *
	 * @param array<string, list<int>> $categories
	 * @return array<string, Person[]>
	 */
	private function hydrate(array $categories): array {
		$ids = [];
		foreach ($categories as $list) {
			foreach ($list as $id) {
				$ids[$id] = true;
			}
		}

		/** @var array<int, Person> $people */
		$people = [];
		foreach (array_keys($ids) as $id) {
			$person = $this->personMapper->findOrNull($id);
			if ($person !== null) {
				$people[$id] = $person;
			}
		}

		$result = [];
		foreach ($categories as $name => $list) {
			$result[$name] = [];
			foreach ($list as $id) {
				if (isset($people[$id])) {
					$result[$name][] = $people[$id];
				}
			}
		}

		return $result;
	}
}
