<script setup lang="ts">
import Check from 'vue-material-design-icons/Check.vue'
import { NcActions, NcActionButton, NcButton, NcTextArea } from '@nextcloud/vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import PencilOutline from 'vue-material-design-icons/PencilOutline.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import TextAccount from 'vue-material-design-icons/TextAccount.vue'
import CakeVariant from 'vue-material-design-icons/CakeVariant.vue'
import GraveStone from 'vue-material-design-icons/GraveStone.vue'
import Account from 'vue-material-design-icons/Account.vue'
import TagMultiple from 'vue-material-design-icons/TagMultiple.vue'
import Close from 'vue-material-design-icons/Close.vue'
import MapMarkerOutline from 'vue-material-design-icons/MapMarkerOutline.vue'
import CalendarOutline from 'vue-material-design-icons/CalendarOutline.vue'
import BookOpenPageVariantOutline from 'vue-material-design-icons/BookOpenPageVariantOutline.vue'
import { storeToRefs } from 'pinia'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import { useEventStore } from '@/stores/use-event-store.ts'
import { useFactStore } from '@/stores/use-fact-store.ts'
import { computed, ref, watch } from 'vue'
import { dateDisplay } from '@/utils/date.ts'
import { placeDisplay } from '@/utils/place.ts'
import { FACT_TYPE_OPTIONS } from '@/constants.ts'
import { deleteFact as deleteFactApi } from '@/api/facts.ts'
import type { Fact } from '@/types'
import { showConfirmation, showError } from '@nextcloud/dialogs'
import CitationList from '@/components/citations/CitationList.vue'
import CiteAction from '@/components/citations/CiteAction.vue'

const peopleStore = usePeopleStore()
const { selectedPerson } = storeToRefs(peopleStore)
const { updateBiography } = peopleStore
const { openLifeDateForm } = useEventStore()
const factStore = useFactStore()
const { facts, loading: loadingFacts } = storeToRefs(factStore)
const { openCreateFactForm, openEditFactForm } = factStore

const birthLabel = computed(() => dateDisplay(selectedPerson.value?.birthDate))
const deathLabel = computed(() => dateDisplay(selectedPerson.value?.deathDate))
const birthPlaceLabel = computed(() => placeDisplay(selectedPerson.value?.birthPlace))
const deathPlaceLabel = computed(() => placeDisplay(selectedPerson.value?.deathPlace))
const birthEventId = computed(() => selectedPerson.value?.birthEventId ?? null)
const deathEventId = computed(() => selectedPerson.value?.deathEventId ?? null)

const editingBiography = ref(false)
const biographyDraft = ref('')
const savingBiography = ref(false)

const factTypeLabel = (type: string) =>
	FACT_TYPE_OPTIONS.find((option) => option.value === type)?.label
	?? type.replace(/_/g, ' ')

watch(selectedPerson, () => {
	editingBiography.value = false
	biographyDraft.value = ''
})

function openCreateFact() {
	const id = selectedPerson.value?.id
	if (id) {
		openCreateFactForm(id)
	}
}

function openBirthForm() {
	const id = selectedPerson.value?.id
	if (id) {
		openLifeDateForm('birth', { id })
	}
}

function openDeathForm() {
	const id = selectedPerson.value?.id
	if (id) {
		openLifeDateForm('death', { id })
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
		await updateBiography(biographyDraft.value || null)
		editingBiography.value = false
	} catch (e) {
		console.error('Failed to save biography', e)
	} finally {
		savingBiography.value = false
	}
}

async function onDeleteFact(fact: Fact) {
	const confirmed = await showConfirmation({
		name: 'Are you sure?',
		text: 'This action will delete this fact and cannot be undone.',
		labelReject: 'Cancel',
	})

	if (!confirmed) {
		return
	}

	try {
		await deleteFactApi(fact.id)
		factStore.removeFact(fact.id)
	} catch (e) {
		showError('Failed to delete fact')
	}
}
</script>

<template>
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
							<Plus v-if="!birthLabel && !birthPlaceLabel" :size="16" />
							<PencilOutline v-else :size="16" />
						</template>
						{{ birthLabel || birthPlaceLabel ? 'Edit' : 'Add' }}
					</NcButton>
				</div>
				<div v-if="birthLabel || birthPlaceLabel" class="life-details">
					<div v-if="birthLabel" class="life-details__row">
						<CalendarOutline :size="14" class="life-details__icon" />
						<span>{{ birthLabel }}</span>
					</div>
					<div v-if="birthPlaceLabel" class="life-details__row">
						<MapMarkerOutline :size="14" class="life-details__icon" />
						<span>{{ birthPlaceLabel }}</span>
					</div>
				</div>
				<div v-else class="sidebar-section__empty">
					No birth details recorded
				</div>
				<CitationList
					v-if="birthEventId"
					subject-type="event"
					:subject-id="birthEventId"
					compact />
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
							<Plus v-if="!deathLabel && !deathPlaceLabel" :size="16" />
							<PencilOutline v-else :size="16" />
						</template>
						{{ deathLabel || deathPlaceLabel ? 'Edit' : 'Add' }}
					</NcButton>
				</div>
				<div v-if="deathLabel || deathPlaceLabel" class="life-details">
					<div v-if="deathLabel" class="life-details__row">
						<CalendarOutline :size="14" class="life-details__icon" />
						<span>{{ deathLabel }}</span>
					</div>
					<div v-if="deathPlaceLabel" class="life-details__row">
						<MapMarkerOutline :size="14" class="life-details__icon" />
						<span>{{ deathPlaceLabel }}</span>
					</div>
				</div>
				<div v-else class="sidebar-section__empty">
					No death details recorded
				</div>
				<CitationList
					v-if="deathEventId"
					subject-type="event"
					:subject-id="deathEventId"
					compact />
			</div>
		</div>

		<div class="sidebar-section">
			<TagMultiple :size="20" class="sidebar-section__icon" />
			<div class="sidebar-section__body">
				<div class="sidebar-section__header">
					<label class="sidebar-section__label">Facts</label>
					<NcButton
						size="small"
						@click="openCreateFact">
						<template #icon>
							<Plus :size="16" />
						</template>
						Add
					</NcButton>
				</div>

				<div v-if="loadingFacts" class="sidebar-section__empty">
					Loading facts...
				</div>
				<div v-else-if="facts.length === 0" class="sidebar-section__empty">
					No facts recorded
				</div>
				<ul v-else class="facts-list">
					<li
						v-for="fact in facts"
						:key="fact.id"
						class="facts-list__item">
						<div class="facts-list__top">
							<span class="facts-list__type">{{ factTypeLabel(fact.type) }}</span>
							<NcActions class="facts-list__actions">
								<CiteAction subject-type="fact" :subject-id="fact.id" />
								<NcActionButton @click="openEditFactForm(fact)">
									<template #icon>
										<Pencil :size="20" />
									</template>
									Edit
								</NcActionButton>
								<NcActionButton @click="onDeleteFact(fact)">
									<template #icon>
										<Delete :size="20" />
									</template>
									Delete
								</NcActionButton>
							</NcActions>
						</div>

						<div v-if="fact.value" class="facts-list__value">
							{{ fact.value }}
						</div>

						<div
							v-if="dateDisplay(fact.date) || placeDisplay(fact.place)"
							class="facts-list__meta">
							<span v-if="dateDisplay(fact.date)" class="facts-list__meta-item">
								<CalendarOutline :size="14" />
								{{ dateDisplay(fact.date) }}
							</span>
							<span v-if="placeDisplay(fact.place)" class="facts-list__meta-item">
								<MapMarkerOutline :size="14" />
								{{ placeDisplay(fact.place) }}
							</span>
						</div>

						<div v-if="fact.note" class="facts-list__note">
							{{ fact.note }}
						</div>

						<CitationList
							subject-type="fact"
							:subject-id="fact.id"
							compact />
					</li>
				</ul>
			</div>
		</div>

		<div v-if="selectedPerson?.id" class="sidebar-section">
			<BookOpenPageVariantOutline :size="20" class="sidebar-section__icon" />
			<div class="sidebar-section__body">
				<label class="sidebar-section__label">Person sources</label>
				<CitationList
					subject-type="person"
					:subject-id="selectedPerson.id"
					compact />
			</div>
		</div>

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

.sidebar-section__hint {
	margin: 0.35rem 0 0;
	font-size: 0.78rem;
	color: var(--color-text-maxcontrast);
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
	justify-content: end;
	gap: .5rem;
}

.life-details {
	display: flex;
	flex-direction: column;
	gap: .25rem;
}

.life-details__row {
	display: flex;
	align-items: flex-start;
	gap: .35rem;
	color: var(--color-main-text);
	line-height: 1.45;
	font-size: .95rem;
}

.life-details__icon {
	flex-shrink: 0;
	margin-top: .15rem;
	color: var(--color-text-maxcontrast);
}

.facts-list {
	list-style: none;
	padding: 0;
	margin: .35rem 0 0;
	display: flex;
	flex-direction: column;
	gap: .65rem;
}

.facts-list__item {
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	padding: .7rem .8rem;
	background: var(--color-main-background);
	transition: border-color .15s ease;
}

.facts-list__item:hover {
	border-color: var(--color-primary-element);
}

.facts-list__top {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: .5rem;
}

.facts-list__type {
	font-size: .72rem;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: .03em;
	color: var(--color-text-maxcontrast);
}

.facts-list__actions {
	margin: -.35rem -.4rem 0 0;
}

.facts-list__value {
	margin-top: .3rem;
	color: var(--color-main-text);
	line-height: 1.45;
	font-weight: 500;
}

.facts-list__meta {
	display: flex;
	flex-direction: column;
	gap: .2rem;
	margin-top: .4rem;
}

.facts-list__meta-item {
	display: flex;
	align-items: flex-start;
	gap: .3rem;
	font-size: .85rem;
	line-height: 1.4;
	color: var(--color-text-maxcontrast);
}

.facts-list__note {
	margin-top: .4rem;
	font-size: .85rem;
	color: var(--color-text-maxcontrast);
	line-height: 1.4;
}
</style>
