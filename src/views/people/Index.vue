<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { NcButton, NcEmptyContent, NcLoadingIcon } from '@nextcloud/vue'
import Account from 'vue-material-design-icons/Account.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import PersonCard from '@/components/PersonCard.vue'
import PersonModal from '@/components/modals/PersonModal.vue'
import { usePersonSidebarStore } from '@/stores/use-person-sidebar-store.ts'
import { usePermissionsStore } from '@/stores/use-permissions-store.ts'
import { deletePerson as deletePersonApi, fetchPeople } from '@/api/people.ts'
import { showDeleteConfirmationDialog } from '@/utils/dialogs.ts'

const people = ref<Array<Record<string, any>>>([])
const loading = ref(true)

const modalOpen = ref(false)
const editing = ref<Record<string, any> | null>(null)

const sidebarStore = usePersonSidebarStore()
const { canCreatePeople, canEdit, canDelete } = storeToRefs(usePermissionsStore())

async function loadPeople() {
	loading.value = true
	try {
		people.value = await fetchPeople()
	} catch (e) {
		console.error('Failed to load people', e)
	} finally {
		loading.value = false
	}
}

function openCreate() {
	editing.value = null
	modalOpen.value = true
}

function openEdit(person: Record<string, any>) {
	editing.value = person
	modalOpen.value = true
}

function onSaved() {
	loadPeople()
}

async function deletePerson(person: Record<string, any>) {
	const confirmed = await showDeleteConfirmationDialog()

	if (!confirmed) {
		return
	}

	try {
		await deletePersonApi(person.id)
		people.value = people.value.filter((p) => p.id !== person.id)
	} catch (e) {
		console.error('Failed to delete person', e)
	}
}

onMounted(loadPeople)
</script>

<template>
	<div class="people">
		<div v-if="canCreatePeople" class="people__header">
			<NcButton variant="primary" @click="openCreate">
				<template #icon>
					<Plus :size="20" />
				</template>
				New person
			</NcButton>
		</div>

		<NcLoadingIcon v-if="loading" :size="32" class="people__loading" />

		<NcEmptyContent
			v-else-if="people.length === 0"
			name="No people yet"
			description="Add your first person to get started.">
			<template #icon>
				<Account />
			</template>
			<template v-if="canCreatePeople" #action>
				<NcButton variant="primary" @click="openCreate">
					New person
				</NcButton>
			</template>
		</NcEmptyContent>

		<div v-else class="people__list">
			<PersonCard
				v-for="person in people"
				:key="person.id"
				:person="person"
				:can-edit="canEdit"
				:can-delete="canDelete"
				@edit="openEdit(person)"
				@delete="deletePerson(person)"
				@click="sidebarStore.openPerson(person)" />
		</div>

		<PersonModal
			v-model:open="modalOpen"
			:person="editing"
			@saved="onSaved" />
	</div>
</template>

<style scoped>
.people {
	display: flex;
	flex-direction: column;
}

.people__header {
	display: flex;
	gap: var(--default-grid-baseline);
	align-items: center;
	flex: 0 0;
	max-width: 100%;
	margin-block: var(--app-navigation-padding, 4px);
	margin-inline: calc(var(--default-clickable-area) + 2 * var(--app-navigation-padding, 4px)) var(--app-navigation-padding, 4px);
}

.people__loading {
	margin-top: 32px;
}

.people__list {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(min(100%, 340px), 1fr));
	gap: 16px;
	margin: 16px;
}
</style>
