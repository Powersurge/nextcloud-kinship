<script setup lang="ts">
import { computed } from 'vue'

import {
	NcAppSidebar,
	NcAppSidebarTab,
	NcActions,
	NcActionButton,
} from '@nextcloud/vue'

import InformationOutline from 'vue-material-design-icons/InformationOutline.vue'
import AccountGroup from 'vue-material-design-icons/AccountGroup.vue'
import TimelineClock from 'vue-material-design-icons/TimelineClock.vue'
import History from 'vue-material-design-icons/History.vue'

import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import CakeVariant from 'vue-material-design-icons/CakeVariant.vue'
import Cross from 'vue-material-design-icons/Cross.vue'
import MapMarker from 'vue-material-design-icons/MapMarker.vue'
import Account from 'vue-material-design-icons/Account.vue'
import Home from 'vue-material-design-icons/Home.vue'

const props = defineProps<{
	open: boolean
	person: Record<string, any>
}>()

const emit = defineEmits([
	'update:open',
	'edit',
	'delete',
	'select-person',
])

const sidebarOpen = computed({
	get: () => props.open,
	set: (v) => emit('update:open', v),
})

const fullName = computed(() =>
	[
		props.person.firstName,
		props.person.middleName,
		props.person.lastName,
	].filter(Boolean).join(' '),
)

const status = computed(() => {
	switch (props.person.livingStatus) {
	case 'deceased':
		return 'Deceased'
	case 'living':
		return 'Living'
	default:
		return 'Unknown'
	}
})

const birthLabel = computed(() =>
	props.person.birthDate?.display || props.person.birthDate?.start || '',
)
const deathLabel = computed(() =>
	props.person.deathDate?.display || props.person.deathDate?.start || '',
)
</script>

<template>
	<NcAppSidebar
		v-model:open="sidebarOpen"
		:name="fullName"
		:subname="status"
		:background="person.avatar || 'https://placehold.co/600x300/e5e7eb/6b7280?text=Person'">
		<template #headerActions>
			<NcActions>
				<NcActionButton @click="$emit('edit')">
					<template #icon>
						<Pencil :size="20" />
					</template>
					Edit
				</NcActionButton>

				<NcActionButton @click="$emit('delete')">
					<template #icon>
						<Delete :size="20" />
					</template>
					Delete
				</NcActionButton>
			</NcActions>
		</template>

		<!-- ================================================= -->
		<!-- Overview -->
		<!-- ================================================= -->

		<NcAppSidebarTab
			id="overview"
			name="Overview">
			<template #icon>
				<InformationOutline :size="20" />
			</template>

			<div class="section">
				<div class="row">
					<Account :size="18" />
					<div>
						<label>Gender</label>
						<div>{{ person.gender || 'Unknown' }}</div>
					</div>
				</div>

				<div
					v-if="birthLabel"
					class="row">
					<CakeVariant :size="18" />

					<div>
						<label>Birth</label>

						<div>{{ birthLabel }}</div>
					</div>
				</div>

				<div
					v-if="deathLabel"
					class="row">
					<Cross :size="18" />

					<div>
						<label>Death</label>

						<div>{{ deathLabel }}</div>
					</div>
				</div>

				<div
					v-if="person.residence"
					class="row">
					<Home :size="18" />

					<div>
						<label>Residence</label>
						<div>{{ person.residence }}</div>
					</div>
				</div>

				<div
					v-if="person.occupation"
					class="row">
					<MapMarker :size="18" />

					<div>
						<label>Occupation</label>
						<div>{{ person.occupation }}</div>
					</div>
				</div>
			</div>

			<div
				v-if="person.notes"
				class="section">
				<h3>Notes</h3>

				<p class="notes">
					{{ person.notes }}
				</p>
			</div>
		</NcAppSidebarTab>

		<!-- ================================================= -->
		<!-- Family -->
		<!-- ================================================= -->

		<NcAppSidebarTab
			id="family"
			name="Family">
			<template #icon>
				<AccountGroup :size="20" />
			</template>
		</NcAppSidebarTab>

		<!-- ================================================= -->
		<!-- Timeline -->
		<!-- ================================================= -->

		<NcAppSidebarTab
			id="timeline"
			name="Timeline">
			<template #icon>
				<TimelineClock :size="20" />
			</template>
		</NcAppSidebarTab>
	</NcAppSidebar>
</template>

<style scoped>
.section {
	padding: 20px;
}

.section + .section {
	border-top: 1px solid var(--color-border);
}

.row {
	display: flex;
	align-items: flex-start;
	gap: 12px;
	margin-bottom: 18px;
}

.row label {
	display: block;
	font-size: .75rem;
	font-weight: 600;
	color: var(--color-text-maxcontrast);
	text-transform: uppercase;
	margin-bottom: 2px;
}

.secondary {
	color: var(--color-text-maxcontrast);
	font-size: .9rem;
}

.notes {
	margin: 8px 0 0;
	line-height: 1.6;
}

.family-group + .family-group {
	margin-top: 24px;
}

.family-group h3 {
	margin: 0 0 10px;
	font-size: .95rem;
}

.person-link {
	display: block;
	width: 100%;
	padding: 8px 10px;
	margin-bottom: 6px;

	text-align: left;

	border: none;
	border-radius: var(--border-radius);

	background: var(--color-background-hover);

	cursor: pointer;
}

.person-link:hover {
	background: var(--color-primary-light);
}

.timeline-item {
	padding: 12px 0;
	border-bottom: 1px solid var(--color-border);
}

.timeline-date {
	font-size: .8rem;
	font-weight: 600;
	color: var(--color-primary);
	margin-bottom: 4px;
}

.timeline-title {
	font-weight: 500;
}
</style>
