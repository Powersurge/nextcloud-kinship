import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import type { FamilyRelationship, PersonFamily } from '@/types'

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
			const { data } = await axios.get(
				generateUrl(`/apps/kinship/api/v1/people/${id}/family`),
			)
			family.value = {
				personId: data.personId ?? id,
				activePartnerships: data.activePartnerships ?? [],
				endedPartnerships: data.endedPartnerships ?? [],
				children: data.children ?? [],
				parents: data.parents ?? [],
			}
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
