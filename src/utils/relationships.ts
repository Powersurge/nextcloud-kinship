/**
 * Relationship type labels. These mirror the accepted input types on the
 * backend (lib/Constants/RelationshipType.php). The stored form is canonical;
 * child-side labels are normalized server-side.
 *
 * Semantics: "Person A is the <type> of Person B".
 */
export const relationshipTypeOptions = [
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
	{ value: 'spouse', label: 'Spouse' },
	{ value: 'former_spouse', label: 'Former spouse' },
	{ value: 'partner', label: 'Partner' },
]

const labelByValue: Record<string, string> = Object.fromEntries(
	relationshipTypeOptions.map((o) => [o.value, o.label]),
)

export function relationshipTypeLabel(type: string): string {
	return labelByValue[type] ?? type
}
