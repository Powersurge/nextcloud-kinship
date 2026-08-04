import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import type { Person } from '@/types'
import {
	fetchFamily,
	fetchFamilyMembers,
	type FamilyMember,
} from '@/api/families.ts'
import { fetchPerson } from '@/api/people.ts'
import {
	fetchPersonDerived,
	type DerivedRelationships,
} from '@/api/relationships.ts'

export type FamilyRelativeBundle = {
	person: Person
	parents: Person[]
	siblings: Person[]
}

export const useFamilyDetailStore = defineStore('familyDetailStore', () => {
	const familyId = ref<number | null>(null)
	const family = ref<Record<string, unknown> | null>(null)
	const members = ref<FamilyMember[]>([])
	const peopleById = ref<Record<number, Person>>({})
	const natalByPersonId = ref<Record<number, DerivedRelationships>>({})
	const loading = ref(false)
	const error = ref<string | null>(null)

	const parents = computed(() =>
		members.value
			.filter((member) => member.role === 'parent')
			.map((member) => peopleById.value[member.personId])
			.filter((person): person is Person => Boolean(person)),
	)

	const children = computed(() =>
		members.value
			.filter((member) => member.role === 'child')
			.map((member) => peopleById.value[member.personId])
			.filter((person): person is Person => Boolean(person)),
	)

	const natalBundles = computed((): FamilyRelativeBundle[] =>
		parents.value.map((person) => {
			const derived = natalByPersonId.value[person.id]
			return {
				person,
				parents: derived?.parents ?? [],
				siblings: derived?.siblings ?? [],
			}
		}),
	)

	async function load(id: number) {
		familyId.value = id
		loading.value = true
		error.value = null

		try {
			const [familyData, memberRows] = await Promise.all([
				fetchFamily(id),
				fetchFamilyMembers(id),
			])

			family.value = familyData
			members.value = memberRows

			const personIds = [...new Set(memberRows.map((member) => member.personId))]
			const people = await Promise.all(
				personIds.map(async (personId) => {
					try {
						return await fetchPerson(personId)
					} catch (e) {
						console.error('Failed to load family member person', personId, e)
						return null
					}
				}),
			)

			const nextPeople: Record<number, Person> = {}
			for (const person of people) {
				if (person?.id) {
					nextPeople[person.id] = person
				}
			}
			peopleById.value = nextPeople

			const parentIds = memberRows
				.filter((member) => member.role === 'parent')
				.map((member) => member.personId)

			const derivedEntries = await Promise.all(
				parentIds.map(async (personId) => {
					try {
						return [personId, await fetchPersonDerived(personId)] as const
					} catch (e) {
						console.error('Failed to load derived relationships', personId, e)
						return [personId, {
							parents: [],
							children: [],
							spouses: [],
							formerSpouses: [],
							siblings: [],
							grandparents: [],
							grandchildren: [],
						} satisfies DerivedRelationships] as const
					}
				}),
			)

			const nextNatal: Record<number, DerivedRelationships> = {}
			for (const [personId, derived] of derivedEntries) {
				nextNatal[personId] = derived
			}
			natalByPersonId.value = nextNatal
		} catch (e) {
			console.error('Failed to load family detail', e)
			family.value = null
			members.value = []
			peopleById.value = {}
			natalByPersonId.value = {}
			error.value = 'Could not load this family.'
		} finally {
			loading.value = false
		}
	}

	function reset() {
		familyId.value = null
		family.value = null
		members.value = []
		peopleById.value = {}
		natalByPersonId.value = {}
		loading.value = false
		error.value = null
	}

	return {
		familyId,
		family,
		members,
		peopleById,
		natalByPersonId,
		loading,
		error,
		parents,
		children,
		natalBundles,
		load,
		reset,
	}
})
