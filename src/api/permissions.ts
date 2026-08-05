import axios from '@nextcloud/axios'
import { apiUrl } from './client'

export type PermissionRole = 'owner' | 'editor' | 'contributor' | 'viewer'

export type PermissionCapabilities = {
	view: boolean
	create_people: boolean
	edit: boolean
	add_sources: boolean
	add_notes: boolean
	add_claims: boolean
	manage_participants: boolean
	accept_claims: boolean
	merge: boolean
	delete: boolean
	manage_custom_events: boolean
	manage_places: boolean
	manage_permissions: boolean
}

export type MyPermissions = {
	uid: string | null
	role: PermissionRole | null
	capabilities: Partial<PermissionCapabilities>
}

export type PermissionGrant = {
	id: number
	uid: string
	role: PermissionRole
	scopeType: string
	scopeId: number | null
	grantedBy: string | null
	createdAt: number | null
	updatedAt: number | null
	displayName?: string
}

export type UserSearchResult = {
	uid: string
	displayName: string
}

export async function fetchMyPermissions(): Promise<MyPermissions> {
	const { data } = await axios.get<MyPermissions>(apiUrl('/permissions/me'))
	return data
}

export async function fetchPermissionGrants(): Promise<PermissionGrant[]> {
	const { data } = await axios.get<{ permissions: PermissionGrant[] }>(
		apiUrl('/permissions'),
	)
	return data.permissions
}

export async function grantPermission(
	uid: string,
	role: PermissionRole,
): Promise<PermissionGrant> {
	const { data } = await axios.post<PermissionGrant>(apiUrl('/permissions'), {
		data: { uid, role },
	})
	return data
}

export async function revokePermission(id: number): Promise<void> {
	await axios.delete(apiUrl(`/permissions/${id}`))
}

export async function searchUsers(search: string): Promise<UserSearchResult[]> {
	const { data } = await axios.get<{ users: UserSearchResult[] }>(
		apiUrl('/admin/users'),
		{ params: { search } },
	)
	return data.users
}
