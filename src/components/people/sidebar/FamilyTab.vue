<script setup lang="ts">
import { ref } from 'vue'
import { NcAppSidebarTab, NcButton, NcLoadingIcon } from '@nextcloud/vue'
import { storeToRefs } from 'pinia'
import Plus from 'vue-material-design-icons/Plus.vue'
import AccountGroup from 'vue-material-design-icons/AccountGroup.vue'
import { useRelationshipStore } from '@/stores/use-relationship-store.ts'
import RelationshipModal from '@/components/modals/RelationshipModal.vue'
import { relationshipTypeLabel } from '@/utils/relationships.ts'
import { dateDisplay } from '@/utils/date.ts'
import { deleteRelationship as deleteRelationshipApi } from '@/api/relationships'

const relationshipStore = useRelationshipStore()
const { personId, family, loading } = storeToRefs(relationshipStore)
const { removeRelationship, refresh } = relationshipStore

const modalOpen = ref(false)
const editing = ref<Record<string, any> | null>(null)
const defaultPersonA = ref<number | null>(null)
const defaultType = ref<string | null>(null)

function openCreatePartnership() {
	editing.value = null
	defaultPersonA.value = personId.value
	defaultType.value = 'married'
	modalOpen.value = true
}

function openCreateChild() {
	editing.value = null
	defaultPersonA.value = personId.value
	defaultType.value = 'parent'
	modalOpen.value = true
}

function openEdit(relationship: Record<string, any>) {
	editing.value = relationship
	defaultPersonA.value = null
	defaultType.value = null
	modalOpen.value = true
}

async function deleteRelationship(relationship: Record<string, any>) {
	if (!window.confirm('Delete this relationship?')) {
		return
	}
	try {
		await deleteRelationshipApi(relationship.id)
		removeRelationship(relationship.id)
	} catch (e) {
		console.error('Failed to delete relationship', e)
	}
}

function onSaved() {
	refresh()
}
</script>

<template>
	<NcAppSidebarTab
		id="family"
		:order="3"
		name="Family">
		<template #icon>
			<AccountGroup :size="20" />
		</template>

		<div class="family-tab">
			<NcLoadingIcon v-if="loading" :size="24" />

			<template v-else>
				<section class="family-section">
					<div class="family-section__header">
						<h3 class="family-section__title">
							Partnerships
						</h3>
						<NcButton size="small" @click="openCreatePartnership">
							<template #icon>
								<Plus :size="16" />
							</template>
							Add
						</NcButton>
					</div>

					<ul v-if="family.activePartnerships.length" class="family-list">
						<li
							v-for="relationship in family.activePartnerships"
							:key="relationship.id"
							class="family-list__item">
							<div class="family-list__main">
								<span class="family-list__name">
									{{ relationship.relatedPersonName }}
								</span>
								<span class="family-list__badge">
									{{ relationshipTypeLabel(relationship.type) }}
								</span>
							</div>
							<p v-if="dateDisplay(relationship.date)" class="family-list__meta">
								{{ dateDisplay(relationship.date) }}
							</p>
							<div class="family-list__actions">
								<NcButton size="small" @click="openEdit(relationship)">
									Edit
								</NcButton>
								<NcButton size="small" @click="deleteRelationship(relationship)">
									Delete
								</NcButton>
							</div>
						</li>
					</ul>
					<p v-else class="family-section__empty">
						No active partnerships
					</p>
				</section>

				<section v-if="family.endedPartnerships.length" class="family-section">
					<h3 class="family-section__title">
						Former partnerships
					</h3>
					<ul class="family-list">
						<li
							v-for="relationship in family.endedPartnerships"
							:key="relationship.id"
							class="family-list__item family-list__item--ended">
							<div class="family-list__main">
								<span class="family-list__name">
									{{ relationship.relatedPersonName }}
								</span>
								<span class="family-list__badge family-list__badge--ended">
									{{ relationshipTypeLabel(relationship.type) }}
								</span>
							</div>
							<p v-if="dateDisplay(relationship.date)" class="family-list__meta">
								{{ dateDisplay(relationship.date) }}
							</p>
							<div class="family-list__actions">
								<NcButton size="small" @click="openEdit(relationship)">
									Edit
								</NcButton>
								<NcButton size="small" @click="deleteRelationship(relationship)">
									Delete
								</NcButton>
							</div>
						</li>
					</ul>
				</section>

				<section class="family-section">
					<div class="family-section__header">
						<h3 class="family-section__title">
							Children
						</h3>
						<NcButton size="small" @click="openCreateChild">
							<template #icon>
								<Plus :size="16" />
							</template>
							Add
						</NcButton>
					</div>

					<ul v-if="family.children.length" class="family-list">
						<li
							v-for="relationship in family.children"
							:key="relationship.id"
							class="family-list__item">
							<div class="family-list__main">
								<span class="family-list__name">
									{{ relationship.relatedPersonName }}
								</span>
								<span class="family-list__badge family-list__badge--muted">
									{{ relationshipTypeLabel(relationship.type) }}
								</span>
							</div>
							<div class="family-list__actions">
								<NcButton size="small" @click="openEdit(relationship)">
									Edit
								</NcButton>
								<NcButton size="small" @click="deleteRelationship(relationship)">
									Delete
								</NcButton>
							</div>
						</li>
					</ul>
					<p v-else class="family-section__empty">
						No children recorded
					</p>
				</section>

				<section class="family-section">
					<h3 class="family-section__title">
						Parents
					</h3>

					<ul v-if="family.parents.length" class="family-list">
						<li
							v-for="relationship in family.parents"
							:key="relationship.id"
							class="family-list__item">
							<div class="family-list__main">
								<span class="family-list__name">
									{{ relationship.relatedPersonName }}
								</span>
								<span class="family-list__badge family-list__badge--muted">
									{{ relationshipTypeLabel(relationship.type) }}
								</span>
							</div>
							<div class="family-list__actions">
								<NcButton size="small" @click="openEdit(relationship)">
									Edit
								</NcButton>
								<NcButton size="small" @click="deleteRelationship(relationship)">
									Delete
								</NcButton>
							</div>
						</li>
					</ul>
					<p v-else class="family-section__empty">
						No parents recorded
					</p>
				</section>
			</template>
		</div>

		<RelationshipModal
			v-model:open="modalOpen"
			:relationship="editing"
			:default-person-a="defaultPersonA"
			:default-type="defaultType"
			@saved="onSaved" />
	</NcAppSidebarTab>
</template>

<style scoped>
.family-tab {
	display: flex;
	flex-direction: column;
	gap: 1.5rem;
	padding: 0.5rem 0;
}

.family-section {
	display: flex;
	flex-direction: column;
	gap: 0.75rem;
}

.family-section__header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 0.5rem;
}

.family-section__title {
	margin: 0;
	font-size: 0.95rem;
	font-weight: 700;
}

.family-section__empty {
	margin: 0;
	color: var(--color-text-maxcontrast);
	font-size: 0.9rem;
	font-style: italic;
}

.family-list {
	list-style: none;
	margin: 0;
	padding: 0;
	display: flex;
	flex-direction: column;
	gap: 0.5rem;
}

.family-list__item {
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	padding: 0.65rem 0.75rem;
}

.family-list__item--ended {
	opacity: 0.85;
}

.family-list__main {
	display: flex;
	align-items: center;
	gap: 0.5rem;
	flex-wrap: wrap;
}

.family-list__name {
	font-weight: 600;
}

.family-list__badge {
	font-size: 0.75rem;
	font-weight: 600;
	padding: 2px 8px;
	border-radius: var(--border-radius-pill);
	background: var(--color-primary-element-light);
}

.family-list__badge--ended {
	background: var(--color-background-dark);
	color: var(--color-text-maxcontrast);
}

.family-list__badge--muted {
	background: var(--color-background-dark);
	color: var(--color-main-text);
}

.family-list__meta {
	margin: 0.25rem 0 0;
	font-size: 0.85rem;
	color: var(--color-text-maxcontrast);
}

.family-list__actions {
	display: flex;
	gap: 0.5rem;
	margin-top: 0.5rem;
}
</style>
