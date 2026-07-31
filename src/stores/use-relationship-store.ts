import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export const useRelationshipStore = defineStore('relationshipStore', () => {
	const personId = ref<number | null>(null)
	const relationships = ref<Array<Record<string, any>>>([])
	const peopleById = ref<Record<number, string>>({})
	const loading = ref(false)

	function reset() {
		personId.value = null
		relationships.value = []
		loading.value = false
	}

	async function loadPeopleMap() {
		if (Object.keys(peopleById.value).length > 0) {
			return
		}

		const { data } = await axios.get(generateUrl('/apps/kinship/api/v1/people'))
		const map: Record<number, string> = {}
		for (const p of data.people ?? []) {
			map[p.id] = p.fullName
				|| `${p.firstName ?? ''} ${p.lastName ?? ''}`.trim()
				|| `#${p.id}`
		}
		peopleById.value = map
	}

	async function loadForPerson(id: number) {
		personId.value = id
		loading.value = true

		try {
			await loadPeopleMap()
			const { data } = await axios.get(
				generateUrl(`/apps/kinship/api/v1/people/${id}/relationships`),
			)
			relationships.value = data.relationships ?? []
		} catch (e) {
			console.error('Failed to load person relationships', e)
			relationships.value = []
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

	function nameOf(id: number): string {
		return peopleById.value[id] ?? `#${id}`
	}

	function removeRelationship(id: number) {
		relationships.value = relationships.value.filter((r) => r.id !== id)
	}

	return {
		personId,
		relationships,
		peopleById,
		loading,
		reset,
		loadForPerson,
		refresh,
		nameOf,
		removeRelationship,
	}
})
