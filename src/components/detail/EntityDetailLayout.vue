<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { NcButton, NcAvatar } from '@nextcloud/vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'

export type DetailTab = {
	id: string
	label: string
}

const props = withDefaults(defineProps<{
	title: string
	subtitle?: string
	avatarUrl?: string
	backTo: string
	backLabel?: string
	tabs: DetailTab[]
	activeTab: string
	loading?: boolean
	error?: string | null
	/** Wider content column for rich detail pages. */
	wide?: boolean
}>(), {
	wide: false,
})

const emit = defineEmits<{
	'update:activeTab': [id: string]
}>()

const router = useRouter()

const resolvedBackLabel = computed(() => props.backLabel || 'Back')

function goBack() {
	router.push(props.backTo)
}

function selectTab(id: string) {
	emit('update:activeTab', id)
}
</script>

<template>
	<div
		class="entity-detail"
		:class="{ 'entity-detail--wide': wide }">
		<header class="entity-detail__header">
			<div class="entity-detail__header-main">
				<NcButton
					class="entity-detail__back"
					variant="tertiary"
					@click="goBack">
					<template #icon>
						<ArrowLeft :size="20" />
					</template>
					{{ resolvedBackLabel }}
				</NcButton>
			</div>
		</header>

		<div class="entity-detail__content">
			<slot name="hero" />

			<div class="entity-detail__content-header">
				<div class="entity-detail__content-header-title">
					<div v-if="props.avatarUrl">
						<NcAvatar
							:size="100"
							:url="props.avatarUrl"
							class="avatar"
							:is-no-user="true" />
					</div>

					<div class="entity-detail__titles">
						<h2 class="entity-detail__title">
							{{ title }}
						</h2>
						<p v-if="subtitle" class="entity-detail__subtitle">
							{{ subtitle }}
						</p>
						<p v-if="$slots.lead" class="entity-detail__lead">
							<slot name="lead" />
						</p>
					</div>
				</div>

				<div v-if="$slots.actions" class="entity-detail__actions">
					<slot name="actions" />
				</div>
			</div>

			<div
				v-if="tabs.length"
				class="entity-detail__tabs"
				role="tablist"
				:aria-label="`${title} sections`">
				<NcButton
					v-for="tab in tabs"
					:key="tab.id"
					type="button"
					role="tab"
					:variant="activeTab === tab.id ? 'primary' : 'tertiary-no-background'"
					:aria-selected="activeTab === tab.id"
					@click="selectTab(tab.id)">
					{{ tab.label }}
				</NcButton>
			</div>

			<div class="section entity-detail__body">
				<p v-if="loading" class="entity-detail__status">
					Loading…
				</p>
				<p v-else-if="error" class="entity-detail__status entity-detail__status--error">
					{{ error }}
				</p>
				<slot v-else />
			</div>
		</div>
	</div>
</template>

<style scoped>
.entity-detail {
	width: 100%;
	min-height: 100%;
}

.entity-detail__content-header {
	display: flex;
	justify-content: space-between;
	align-items: start;
}

.entity-detail__content {
	min-height: 100%;
	margin: auto;
	max-width: 924px;
	margin-block: var(--app-navigation-padding, 4px);
	padding-inline: var(--app-navigation-padding, 4px);
}

.entity-detail--wide .entity-detail__content {
	max-width: 1200px;
}

.entity-detail__header {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 1rem;
	flex-wrap: wrap;
	margin-block: var(--app-navigation-padding, 4px);
	padding-inline: calc(var(--default-clickable-area) + var(--app-navigation-padding, 4px)) var(--app-navigation-padding, 4px);
}

.entity-detail__header-main {
	display: flex;
	flex-direction: column;
	align-items: flex-start;
	gap: 0.5rem;
	min-width: 0;
}
.entity-detail__content-header-title {
	display: flex;
	align-items: center;
	gap: 1rem;
}
.entity-detail__titles {
	min-width: 0;
}

.entity-detail__title {
	margin: 0;
	font-size: 1.5rem;
	line-height: 1.3;
	word-break: break-word;
}

.entity-detail__subtitle {
	margin: 0.25rem 0 0;
	color: var(--color-text-maxcontrast);
	font-size: 0.95rem;
}

.entity-detail__lead {
	margin: 0.45rem 0 0;
	color: var(--color-text-maxcontrast);
	font-size: 0.9rem;
	line-height: 1.45;
	max-width: 40rem;
}

.entity-detail__actions {
	display: flex;
	align-items: center;
	gap: 0.5rem;
	flex-wrap: wrap;
}

.entity-detail__tabs {
	display: flex;
	gap: 0.25rem;
	flex-wrap: wrap;
	border-bottom: 1px solid var(--color-border);
	margin: 0.5rem 0 0;
	padding: 0.5rem 0;
}

.entity-detail__body {
	padding: 1.25rem 0.25rem 0;
	flex: 1;
	min-width: 0;
}

.entity-detail__status {
	margin: 1rem 0;
	color: var(--color-text-maxcontrast);
}

.entity-detail__status--error {
	color: var(--color-error);
}
</style>
