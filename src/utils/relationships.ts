/**
 * Relationship type labels. These mirror the accepted input types on the
 * backend (lib/Constants/RelationshipType.php). The stored form is canonical;
 * child-side labels are normalized server-side.
 *
 * Semantics: "Person A is the <type> of Person B" for parent types.
 * Partnership statuses are symmetric between A and B.
 */

export const partnershipTypeOptions = [
	{ value: 'married', label: 'Married' },
	{ value: 'separated', label: 'Separated' },
	{ value: 'engaged', label: 'Engaged' },
	{ value: 'domestic_partnership', label: 'Domestic partnership' },
	{ value: 'partner', label: 'Partner' },
	{ value: 'divorced', label: 'Divorced' },
	{ value: 'annulled', label: 'Annulled' },
	{ value: 'widowed', label: 'Widowed' },
]

export const parentChildTypeOptions = [
	{ value: 'parent', label: 'Parent' },
	{ value: 'child', label: 'Child' },
	{ value: 'biological_parent', label: 'Biological parent' },
	{ value: 'biological_child', label: 'Biological child' },
	{ value: 'adoptive_parent', label: 'Adoptive parent' },
	{ value: 'adopted_child', label: 'Adopted child' },
	{ value: 'foster_parent', label: 'Foster parent' },
	{ value: 'foster_child', label: 'Foster child' },
	{ value: 'guardian', label: 'Guardian' },
	{ value: 'ward', label: 'Ward' },
]

export interface RelationshipTypeOption {
	value: string
	label: string
}

export const RELATIONSHIP_GROUP_PARTNERSHIP = 'partnership'
export const RELATIONSHIP_GROUP_PARENT_CHILD = 'parent_child'

export const SPOUSE_RELATIONSHIP_GROUP_LABEL = 'Spouse / partnership'
export const PARENT_CHILD_RELATIONSHIP_GROUP_LABEL = 'Parent / child'

export const relationshipGroupOptions = [
	{ value: RELATIONSHIP_GROUP_PARTNERSHIP, label: SPOUSE_RELATIONSHIP_GROUP_LABEL },
	{ value: RELATIONSHIP_GROUP_PARENT_CHILD, label: PARENT_CHILD_RELATIONSHIP_GROUP_LABEL },
]

export function relationshipTypeGroup(type: string): string {
	return isPartnershipType(type)
		? RELATIONSHIP_GROUP_PARTNERSHIP
		: RELATIONSHIP_GROUP_PARENT_CHILD
}

export function relationshipTypesForGroup(group: string): RelationshipTypeOption[] {
	return group === RELATIONSHIP_GROUP_PARTNERSHIP
		? partnershipTypeOptions
		: parentChildTypeOptions
}

export function defaultTypeForGroup(group: string): string {
	return group === RELATIONSHIP_GROUP_PARTNERSHIP ? 'married' : 'parent'
}

/** child-side input label => canonical parent-side type (mirrors backend). */
export const CHILD_TO_PARENT: Record<string, string> = {
	child: 'parent',
	biological_child: 'biological_parent',
	adopted_child: 'adoptive_parent',
	foster_child: 'foster_parent',
	ward: 'guardian',
}

const PARENTHOOD_INPUT = new Set([
	'parent',
	'child',
	'biological_parent',
	'biological_child',
	'adoptive_parent',
	'adopted_child',
	'foster_parent',
	'foster_child',
])

/**
 * Input types where an optional co-parent link may be recorded.
 * @param type
 */
export function isParenthoodInputType(type: string): boolean {
	return PARENTHOOD_INPUT.has(type)
}

const PARENTHOOD_STORED = new Set([
	'parent',
	'biological_parent',
	'adoptive_parent',
	'foster_parent',
])

/**
 * Canonical stored parenthood types (person_a is the parent).
 * @param type
 */
export function isParenthoodStoredType(type: string): boolean {
	return PARENTHOOD_STORED.has(type)
}

/**
 * Resolve parent and child person ids from form input before canonicalization.
 * @param personA
 * @param personB
 * @param type
 */
export function parentChildIdsFromInput(
	personA: number | null,
	personB: number | null,
	type: string,
): { parentId: number | null, childId: number | null } {
	if (personA == null || personB == null || !isParenthoodInputType(type)) {
		return { parentId: null, childId: null }
	}

	if (type in CHILD_TO_PARENT) {
		return { parentId: personB, childId: personA }
	}

	return { parentId: personA, childId: personB }
}

export const relationshipTypeOptions = [
	...partnershipTypeOptions,
	...parentChildTypeOptions,
]

const ACTIVE_PARTNERSHIP = new Set([
	'married',
	'separated',
	'engaged',
	'domestic_partnership',
	'partner',
])

const ENDED_PARTNERSHIP = new Set([
	'divorced',
	'annulled',
	'widowed',
])

const PARTNERSHIP = new Set([
	...ACTIVE_PARTNERSHIP,
	...ENDED_PARTNERSHIP,
	'spouse',
	'former_spouse',
])

const labelByValue: Record<string, string> = Object.fromEntries(
	relationshipTypeOptions.map((o) => [o.value, o.label]),
)

labelByValue.spouse = 'Married'
labelByValue.former_spouse = 'Divorced'

export function relationshipTypeLabel(type: string): string {
	return labelByValue[type] ?? type.replace(/_/g, ' ')
}

export function isPartnershipType(type: string): boolean {
	return PARTNERSHIP.has(type)
}

export function isActivePartnershipType(type: string): boolean {
	return ACTIVE_PARTNERSHIP.has(type) || type === 'spouse'
}

export function isEndedPartnershipType(type: string): boolean {
	return ENDED_PARTNERSHIP.has(type) || type === 'former_spouse'
}

export interface TreeSpouseSummary {
	id: number
	firstName: string
	lastName: string
	gender: string
	status: string
}

export interface TreeNode {
	id: number
	firstName: string
	lastName: string
	gender: string
	spouses?: TreeSpouseSummary[]
	spouse?: TreeSpouseSummary
	children?: TreeNode[]
}
