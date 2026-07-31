import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { FamilyRelationship, PersonFamily } from '@/types'
import { fetchPersonFamily } from '@/api/people'

const emptyFamily = (): PersonFamily => ({
	personId: null,
	activePartnerships: [],
	endedPartnerships: [],
	children: [],
	parents: [],
})

export const useRelationshipStore = defineStore('relationshipStore', () => {
	const personId = ref<number | null>(null)
	const family = ref<PersonFamily>(emptyFamily())
	const loading = ref(false)

	function reset() {
		personId.value = null
		family.value = emptyFamily()
		loading.value = false
	}

	async function loadForPerson(id: number) {
		personId.value = id
		loading.value = true

		try {
			family.value = await fetchPersonFamily(id)
		} catch (e) {
			console.error('Failed to load person family', e)
			family.value = { ...emptyFamily(), personId: id }
		} finally {
			loading.value = false
		}
	}

	async function refresh() {
		const id = personId.value
		if (id) {
			await loadForPerson(id)
		}
	}

	function removeRelationship(id: number) {
		const drop = (items: FamilyRelationship[]) => items.filter((r) => r.id !== id)
		family.value = {
			...family.value,
			activePartnerships: drop(family.value.activePartnerships),
			endedPartnerships: drop(family.value.endedPartnerships),
			children: drop(family.value.children),
			parents: drop(family.value.parents),
		}
	}

	return {
		personId,
		family,
		loading,
		reset,
		loadForPerson,
		refresh,
		removeRelationship,
	}
})
