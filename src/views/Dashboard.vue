<script setup lang="ts">
import { NcButton } from '@nextcloud/vue'
import { useRouter } from 'vue-router'
import Account from 'vue-material-design-icons/Account.vue'
import HumanMaleFemaleChild from 'vue-material-design-icons/HumanMaleFemaleChild.vue'
import Calendar from 'vue-material-design-icons/Calendar.vue'
import FamilyTree from 'vue-material-design-icons/FamilyTree.vue'
import VectorLink from 'vue-material-design-icons/VectorLink.vue'

const router = useRouter()

const shortcuts = [
	{
		name: 'People',
		description: 'Manage family members and their details.',
		to: '/people',
		icon: Account,
	},
	{
		name: 'Families',
		description: 'Group people into families.',
		to: '/families',
		icon: HumanMaleFemaleChild,
	},
	{
		name: 'Relationships',
		description: 'Record parents, spouses and partners; the rest is computed.',
		to: '/relationships',
		icon: VectorLink,
	},
	{
		name: 'Events',
		description: 'Track births, marriages and other events.',
		to: '/events',
		icon: Calendar,
	},
	{
		name: 'Family tree',
		description: 'Visualise how everyone is connected.',
		to: '/tree',
		icon: FamilyTree,
	},
]
</script>

<template>
	<div class="dashboard">
		<div class="dashboard__header">
			<h2 class="dashboard__title">
				Kinship
			</h2>
		</div>

		<div class="dashboard__content">
			<p class="dashboard__intro">
				Welcome to your family history workspace.
			</p>

			<div class="dashboard__grid">
				<div
					v-for="shortcut in shortcuts"
					:key="shortcut.to"
					class="dashboard__card">
					<div class="dashboard__card-icon">
						<component :is="shortcut.icon" :size="32" />
					</div>
					<div class="dashboard__card-body">
						<h3 class="dashboard__card-title">
							{{ shortcut.name }}
						</h3>
						<p class="dashboard__card-desc">
							{{ shortcut.description }}
						</p>
						<NcButton @click="router.push(shortcut.to)">
							Open
						</NcButton>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>
.dashboard {
	display: flex;
	flex-direction: column;
}

.dashboard__header {
	display: flex;
	gap: var(--default-grid-baseline);
	align-items: center;
	flex: 0 0;
	max-width: 100%;
	margin-block: var(--app-navigation-padding, 4px);
	margin-inline: calc(var(--default-clickable-area) + 2 * var(--app-navigation-padding, 4px)) var(--app-navigation-padding, 4px);
}

.dashboard__title {
	margin: 0;
}

.dashboard__content {
	margin: 16px;
}

.dashboard__intro {
	margin: 0 0 16px;
	color: var(--color-text-maxcontrast);
}

.dashboard__grid {
	display: grid;
	grid-template-columns: repeat(1, minmax(0px, 1fr));
	gap: 16px;
}

@media (width >= 64rem) {
	.dashboard__grid {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (width >= 80rem) {
	.dashboard__grid {
		grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
		gap: 16px;
	}
}

.dashboard__card {
	display: flex;
	gap: 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large, 8px);
	padding: 16px;
	background-color: var(--color-main-background);
}

.dashboard__card-icon {
	color: var(--color-primary-element);
}

.dashboard__card-title {
	margin: 0 0 4px;
}

.dashboard__card-desc {
	margin: 0 0 12px;
	color: var(--color-text-maxcontrast);
	font-size: 0.9em;
}
</style>
