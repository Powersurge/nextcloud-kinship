<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { NcButton, NcEmptyContent, NcLoadingIcon } from '@nextcloud/vue'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import VectorLink from 'vue-material-design-icons/VectorLink.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import RelationshipCard from '../components/RelationshipCard.vue'
import RelationshipModal from '../components/modals/RelationshipModal.vue'

const relationships = ref<Array<Record<string, any>>>([])
const peopleById = ref<Record<number, string>>({})
const loading = ref(true)

const modalOpen = ref(false)
const editing = ref<Record<string, any> | null>(null)

function nameOf(id: number): string {
	return peopleById.value[id] ?? `#${id}`
}

async function loadAll() {
	loading.value = true
	try {
		const [relRes, peopleRes] = await Promise.all([
			axios.get(generateUrl('/apps/kinship/api/v1/relationships')),
			axios.get(generateUrl('/apps/kinship/api/v1/people')),
		])
		relationships.value = relRes.data.relationships ?? []
		const map: Record<number, string> = {}
		for (const p of peopleRes.data.people ?? []) {
			map[p.id] = `${p.firstName ?? ''} ${p.lastName ?? ''}`.trim() || `#${p.id}`
		}
		peopleById.value = map
	} catch (e) {
		console.error('Failed to load relationships', e)
	} finally {
		loading.value = false
	}
}

const hasRelationships = computed(() => relationships.value.length > 0)

function openCreate() {
	editing.value = null
	modalOpen.value = true
}

function openEdit(relationship: Record<string, any>) {
	editing.value = relationship
	modalOpen.value = true
}

function onSaved() {
	loadAll()
}

async function deleteRelationship(relationship: Record<string, any>) {
	if (!window.confirm('Delete this relationship?')) {
		return
	}
	try {
		await axios.delete(
			generateUrl(`/apps/kinship/api/v1/relationships/${relationship.id}`),
		)
		relationships.value = relationships.value.filter((r) => r.id !== relationship.id)
	} catch (e) {
		console.error('Failed to delete relationship', e)
	}
}

onMounted(loadAll)
</script>

<template>
	<div class="relationships">
		<div class="relationships__header">
			<NcButton variant="primary" @click="openCreate">
				<template #icon>
					<Plus :size="20" />
				</template>
				New relationship
			</NcButton>
		</div>

		<NcLoadingIcon v-if="loading" :size="32" class="relationships__loading" />

		<NcEmptyContent
			v-else-if="!hasRelationships"
			name="No relationships yet"
			description="Record parent, spouse and partner links; everything else is computed.">
			<template #icon>
				<VectorLink />
			</template>
			<template #action>
				<NcButton variant="primary" @click="openCreate">
					New relationship
				</NcButton>
			</template>
		</NcEmptyContent>

		<div v-else class="relationships__list">
			<RelationshipCard
				v-for="relationship in relationships"
				:key="relationship.id"
				:relationship="relationship"
				:a-name="nameOf(relationship.personA)"
				:b-name="nameOf(relationship.personB)"
				@edit="openEdit(relationship)"
				@delete="deleteRelationship(relationship)" />
		</div>

		<RelationshipModal
			v-model:open="modalOpen"
			:relationship="editing"
			@saved="onSaved" />
	</div>
</template>

<style scoped>
.relationships {
	display: flex;
	flex-direction: column;
}

.relationships__header {
	display: flex;
	gap: var(--default-grid-baseline);
	align-items: center;
	flex: 0 0;
	max-width: 100%;
	margin-block: var(--app-navigation-padding, 4px);
	margin-inline: calc(var(--default-clickable-area) + 2 * var(--app-navigation-padding, 4px)) var(--app-navigation-padding, 4px);
}

.relationships__loading {
	margin-top: 32px;
}

.relationships__list {
	display: flex;
	flex-direction: column;
	gap: 8px;
	margin: 16px;
}
</style>
