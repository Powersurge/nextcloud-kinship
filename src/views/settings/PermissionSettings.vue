<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { NcNoteCard, NcSelect, NcSettingsSection, NcCheckboxRadioSwitch, NcButton, NcAvatar } from '@nextcloud/vue'
import type { PermissionGrant, PermissionRole, UserSearchResult } from '@/api/permissions.js'
import type { RoleOption, UserOption } from '@/types/index.js'
import { DEFAULT_ROLE_OPTIONS, GRANT_ROLE_OPTIONS } from '@/constants.ts'
import type { AdminSettings, DefaultRole } from '@/api/settings.js'
import { fetchPermissionGrants, grantPermission, revokePermission, searchUsers } from '@/api/permissions.ts'

const settings = defineModel<AdminSettings>({
	required: true,
})
const emit = defineEmits<{ save: [] }>()

const grantsError = ref('')
const grants = ref<PermissionGrant[]>([])
const grantsLoading = ref(false)
const granting = ref(false)
const userOptions = ref<UserOption[]>([])
const selectedUser = ref<UserOption | null>(null)
const selectedGrantRole = ref<RoleOption | null>(null)

const selectedDefaultRole = computed({
	get: () => DEFAULT_ROLE_OPTIONS.find((option) => option.value === settings.value?.defaultRole)
		?? DEFAULT_ROLE_OPTIONS[1],
	set: (option: RoleOption) => {
		if (settings.value) {
			settings.value.defaultRole = option.value as DefaultRole
		}

		save()
	},
})

async function loadGrants(): Promise<void> {
	grantsLoading.value = true
	grantsError.value = ''
	try {
		grants.value = await fetchPermissionGrants()
	} catch (e) {
		grantsError.value = 'Could not load role grants.'
		console.error('Failed to load permission grants', e)
	} finally {
		grantsLoading.value = false
	}
}

async function onUserSearch(search: string): Promise<void> {
	try {
		const users: UserSearchResult[] = await searchUsers(search)
		userOptions.value = users.map((user) => ({
			...user,
			label: `${user.displayName} (${user.uid})`,
		}))
	} catch (e) {
		console.error('Failed to search users', e)
	}
}

async function onGrant(): Promise<void> {
	if (!selectedUser.value || !selectedGrantRole.value) {
		return
	}

	granting.value = true
	grantsError.value = ''
	try {
		await grantPermission(
			selectedUser.value.uid,
			selectedGrantRole.value.value as PermissionRole,
		)
		selectedUser.value = null
		await loadGrants()
	} catch (e) {
		grantsError.value = 'Could not grant role. Please try again.'
		console.error('Failed to grant permission', e)
	} finally {
		granting.value = false
	}
}

async function onRevoke(grant: PermissionGrant): Promise<void> {
	grantsError.value = ''
	try {
		await revokePermission(grant.id)
		grants.value = grants.value.filter((item) => item.id !== grant.id)
	} catch (e) {
		grantsError.value = 'Could not revoke role. Please try again.'
		console.error('Failed to revoke permission', e)
	}
}

const save = () => {
	emit('save')
}

onMounted(() => {
	selectedGrantRole.value = GRANT_ROLE_OPTIONS[0]
	loadGrants()
	onUserSearch('')
})
</script>

<template>
	<NcSettingsSection
		name="Permissions"
		description="Default access for users without an explicit role grant, and optional editor powers from the Kinship permission matrix.">
		<NcSelect
			v-model="selectedDefaultRole"
			input-label="Default role"
			label="label"
			:options="DEFAULT_ROLE_OPTIONS"
			:clearable="false" />

		<NcCheckboxRadioSwitch
			v-model="settings.editorCanDelete"
			class="admin-settings__switch"
			type="switch"
			@update:model-value="save">
			Editors may delete records
		</NcCheckboxRadioSwitch>

		<NcCheckboxRadioSwitch
			v-model="settings.editorCanAcceptClaims"
			class="admin-settings__switch"
			type="switch"
			@update:model-value="save">
			Editors may accept or reject claims
		</NcCheckboxRadioSwitch>
	</NcSettingsSection>
	<NcSettingsSection
		name="Role grants"
		description="Assign Kinship roles to Nextcloud users. Nextcloud admins always have owner access.">
		<NcNoteCard v-if="grantsError" type="error">
			{{ grantsError }}
		</NcNoteCard>

		<div class="admin-settings__grant-form">
			<NcSelect
				v-model="selectedUser"
				class="admin-settings__field"
				input-label="User"
				label="label"
				:options="userOptions"
				:clearable="true"
				placeholder="Search users…"
				@search="onUserSearch" />

			<NcSelect
				v-model="selectedGrantRole"
				class="admin-settings__field"
				input-label="Role"
				label="label"
				:options="GRANT_ROLE_OPTIONS"
				:clearable="false" />

			<NcButton
				class="admin-settings__button"
				variant="primary"
				:disabled="!selectedUser || !selectedGrantRole || granting"
				@click="onGrant">
				Grant role
			</NcButton>
		</div>

		<p v-if="grantsLoading" class="admin-settings__muted">
			Loading grants…
		</p>

		<table v-else-if="grants.length > 0" class="admin-settings__table">
			<thead>
				<tr>
					<th>User</th>
					<th>Role</th>
					<th />
				</tr>
			</thead>
			<tbody>
				<tr v-for="grant in grants" :key="grant.id">
					<td>
						<div class="admin-settings__user">
							<NcAvatar :user="grant.uid" :display-name="grant.displayName" :size="40" />
							<div class="admin-settings__user-info">
								<span>{{ grant.displayName || grant.uid }}</span>
								<span class="admin-settings__muted">{{ grant.uid }}</span>
							</div>
						</div>
					</td>
					<td class="admin-settings__role">
						{{ grant.role }}
					</td>
					<td class="admin-settings__actions">
						<NcButton variant="error" @click="onRevoke(grant)">
							Revoke
						</NcButton>
					</td>
				</tr>
			</tbody>
		</table>

		<p v-else class="admin-settings__muted">
			No explicit role grants yet. Users receive the default role above.
		</p>
	</NcSettingsSection>
</template>

<style scoped>
.admin-settings__field {
	margin-top: 12px;
}

.admin-settings__switch {
	margin-top: 12px;
}

.admin-settings__grant-form {
	display: flex;
	flex-wrap: wrap;
	gap: 12px;
	align-items: flex-end;
	margin-bottom: 16px;
}

.admin-settings__grant-form .admin-settings__field {
	margin-top: 0;
	min-width: 220px;
	flex: 1;
}

.admin-settings__table {
	width: 100%;
	border-collapse: collapse;
}

.admin-settings__table th,
.admin-settings__table td {
	padding: 8px 12px;
	border-bottom: 1px solid var(--color-border);
	text-align: left;
	vertical-align: middle;
}

.admin-settings__user {
	display: flex;
	align-items: center;
	gap: 6px;
}

.admin-settings__user-info {
	display: flex;
	flex-direction: column;
	gap: 2px;
}

.admin-settings__role {
	text-transform: capitalize;
}

.admin-settings__actions {
	text-align: right;
	width: 1%;
	white-space: nowrap;
}

.admin-settings__muted {
	color: var(--color-text-maxcontrast);
	font-size: 0.9rem;
}

.admin-settings__grant-form .admin-settings__field {
	margin-top: 0;
	min-width: 220px;
	flex: 1;
}

.admin-settings__table th,
.admin-settings__table td {
	padding: 8px 12px;
	border-bottom: 1px solid var(--color-border);
	text-align: left;
	vertical-align: middle;
}

.admin-settings__button {
	margin-bottom: 4px;
}
</style>
