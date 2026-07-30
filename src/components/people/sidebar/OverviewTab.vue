<script setup lang="ts">
import Check from 'vue-material-design-icons/Check.vue'
import { NcAppSidebarTab, NcButton, NcTextArea } from '@nextcloud/vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import PencilOutline from 'vue-material-design-icons/PencilOutline.vue'
import TextAccount from 'vue-material-design-icons/TextAccount.vue'
import CakeVariant from 'vue-material-design-icons/CakeVariant.vue'
import GraveStone from 'vue-material-design-icons/GraveStone.vue'
import InformationOutline from 'vue-material-design-icons/InformationOutline.vue'
import Account from 'vue-material-design-icons/Account.vue'
import Close from 'vue-material-design-icons/Close.vue'
import { storeToRefs } from 'pinia'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import { useEventStore } from '@/stores/use-event-store.ts'
import { computed, ref, watch } from 'vue'
import { dateDisplay } from '@/utils/date.ts'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

const { selectedPerson } = storeToRefs(usePeopleStore())
const { refreshSelectedPerson } = usePeopleStore()
const { openLifeDateForm } = useEventStore()

const birthLabel = computed(() => dateDisplay(selectedPerson.value?.birthDate))
const deathLabel = computed(() => dateDisplay(selectedPerson.value?.deathDate))

const editingBiography = ref(false)
const biographyDraft = ref('')
const savingBiography = ref(false)

watch(selectedPerson, () => {
	editingBiography.value = false
	biographyDraft.value = ''
})

function openBirthForm() {
	if (selectedPerson.value) {
		openLifeDateForm('birth', selectedPerson.value)
	}
}

function openDeathForm() {
	if (selectedPerson.value) {
		openLifeDateForm('death', selectedPerson.value)
	}
}

function startBiographyEdit() {
	biographyDraft.value = selectedPerson.value?.biography ?? ''
	editingBiography.value = true
}

function cancelBiographyEdit() {
	editingBiography.value = false
	biographyDraft.value = ''
}

async function saveBiography() {
	if (!selectedPerson.value?.id) {
		return
	}

	savingBiography.value = true
	try {
		await axios.put(
			generateUrl(`/apps/kinship/api/v1/people/${selectedPerson.value.id}`),
			{ data: { biography: biographyDraft.value || null } },
		)
		await refreshSelectedPerson()
		editingBiography.value = false
	} catch (e) {
		console.error('Failed to save biography', e)
	} finally {
		savingBiography.value = false
	}
}
</script>

<template>
	<NcAppSidebarTab
		id="overview"
		name="Overview">
		<template #icon>
			<InformationOutline :size="20" />
		</template>

		<div class="sidebar-sections">
			<div class="sidebar-section">
				<Account :size="20" class="sidebar-section__icon" />

				<div class="sidebar-section__body">
					<label class="sidebar-section__label">Gender</label>
					<div class="sidebar-section__value capitalize">
						{{ selectedPerson?.gender || 'Unknown' }}
					</div>
				</div>
			</div>

			<div class="sidebar-section">
				<CakeVariant :size="20" class="sidebar-section__icon" />
				<div class="sidebar-section__body">
					<div class="sidebar-section__header">
						<label class="sidebar-section__label">Birth</label>
						<NcButton
							size="small"
							@click="openBirthForm">
							<template #icon>
								<Plus v-if="!birthLabel" :size="16" />
								<PencilOutline v-else :size="16" />
							</template>
							{{ birthLabel ? 'Edit' : 'Add' }}
						</NcButton>
					</div>
					<div v-if="birthLabel" class="sidebar-section__value">
						{{ birthLabel }}
					</div>
					<div v-else class="sidebar-section__empty">
						No birth date recorded
					</div>
				</div>
			</div>

			<div class="sidebar-section">
				<GraveStone :size="20" class="sidebar-section__icon" />
				<div class="sidebar-section__body">
					<div class="sidebar-section__header">
						<label class="sidebar-section__label">Death</label>
						<NcButton
							size="small"
							@click="openDeathForm">
							<template #icon>
								<Plus v-if="!deathLabel" :size="16" />
								<PencilOutline v-else :size="16" />
							</template>
							{{ deathLabel ? 'Edit' : 'Add' }}
						</NcButton>
					</div>
					<div v-if="deathLabel" class="sidebar-section__value">
						{{ deathLabel }}
					</div>
					<div v-else class="sidebar-section__empty">
						No death date recorded
					</div>
				</div>
			</div>

			<div class="sidebar-field">
				<TextAccount :size="20" class="sidebar-field__icon" />
				<div class="sidebar-field__body">
					<div class="sidebar-field__header">
						<label class="sidebar-field__label">Biography</label>
			<div class="sidebar-section">
				<TextAccount :size="20" class="sidebar-section__icon" />
				<div class="sidebar-section__body">
					<div class="sidebar-section__header">
						<label class="sidebar-section__label">Biography</label>
						<NcButton
							v-if="!editingBiography"
							size="small"
							@click="startBiographyEdit">
							<template #icon>
								<PencilOutline :size="16" />
							</template>
							Edit
						</NcButton>
					</div>

					<div v-if="editingBiography" class="sidebar-section__editor">
						<NcTextArea
							v-model="biographyDraft"
							label="Biography"
							:disabled="savingBiography" />
						<div class="sidebar-section__editor-actions">
							<NcButton
								size="small"
								:disabled="savingBiography"
								@click="cancelBiographyEdit">
								<template #icon>
									<Close :size="16" />
								</template>
								Cancel
							</NcButton>
							<NcButton
								size="small"
								variant="primary"
								:disabled="savingBiography"
								@click="saveBiography">
								<template #icon>
									<Check :size="16" />
								</template>
								Save
							</NcButton>
						</div>
					</div>
					<div v-else-if="selectedPerson?.biography" class="sidebar-section__value sidebar-section__value--bio">
						{{ selectedPerson.biography }}
					</div>
					<div v-else class="sidebar-section__empty">
						No biography yet
					</div>
				</div>
			</div>
		</div>
	</NcAppSidebarTab>
</template>

<style scoped>
.sidebar-sections {
	display: flex;
	flex-direction: column;
	gap: 1.5rem;
}

.sidebar-section {
	display: flex;
	justify-content: flex-start;
	gap: 0.5rem;
}

.sidebar-section__icon {
	flex-shrink: 0;
	margin-bottom: auto;
	color: var(--color-text-maxcontrast);
}

.sidebar-section__body {
	flex: 1;
	min-width: 0;
}

.sidebar-section__header {
	display: flex;
	align-items: start;
	justify-content: space-between;
	gap: .5rem;
	margin-bottom: .25rem;
}

.sidebar-section__label {
	font-weight: 700;
}

.sidebar-section__value {
	color: var(--color-main-text);
	line-height: 1.5;
}

.sidebar-section__value--bio {
	white-space: pre-wrap;
}

.sidebar-section__empty {
	color: var(--color-text-maxcontrast);
	font-size: .9rem;
	font-style: italic;
}

.sidebar-section__editor {
	display: flex;
	flex-direction: column;
	gap: .75rem;
}

.sidebar-section__editor-actions {
	display: flex;
	justify-content: flex-end;
	gap: .5rem;
}
</style>
