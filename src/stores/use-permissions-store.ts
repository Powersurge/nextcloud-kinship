import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import {
	fetchMyPermissions,
	type MyPermissions,
	type PermissionCapabilities,
	type PermissionRole,
} from '@/api/permissions.ts'

const emptyCapabilities: PermissionCapabilities = {
	view: false,
	create_people: false,
	edit: false,
	add_sources: false,
	add_notes: false,
	add_claims: false,
	manage_participants: false,
	accept_claims: false,
	merge: false,
	delete: false,
	manage_custom_events: false,
	manage_places: false,
	manage_permissions: false,
}

export const usePermissionsStore = defineStore('permissionsStore', () => {
	const uid = ref<string | null>(null)
	const role = ref<PermissionRole | null>(null)
	const capabilities = ref<PermissionCapabilities>({ ...emptyCapabilities })
	const loaded = ref(false)
	const loading = ref(false)

	const canView = computed(() => capabilities.value.view)
	const canCreatePeople = computed(() => capabilities.value.create_people)
	const canEdit = computed(() => capabilities.value.edit)
	const canDelete = computed(() => capabilities.value.delete)
	const canAddSources = computed(() => capabilities.value.add_sources)

	function can(action: keyof PermissionCapabilities): boolean {
		return Boolean(capabilities.value[action])
	}

	async function load(): Promise<MyPermissions | null> {
		if (loading.value) {
			return null
		}

		loading.value = true
		try {
			const data = await fetchMyPermissions()
			uid.value = data.uid
			role.value = data.role
			capabilities.value = {
				...emptyCapabilities,
				...data.capabilities,
			}
			loaded.value = true
			return data
		} catch (e) {
			console.error('Failed to load permissions', e)
			capabilities.value = { ...emptyCapabilities }
			role.value = null
			loaded.value = true
			return null
		} finally {
			loading.value = false
		}
	}

	return {
		uid,
		role,
		capabilities,
		loaded,
		loading,
		canView,
		canCreatePeople,
		canEdit,
		canDelete,
		canAddSources,
		can,
		load,
	}
})
