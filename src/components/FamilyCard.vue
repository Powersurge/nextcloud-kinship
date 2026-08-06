<script setup lang="ts">
import { computed } from 'vue'
import HumanMaleFemaleChild from 'vue-material-design-icons/HumanMaleFemaleChild.vue'

const props = withDefaults(defineProps<{
	family: Record<string, any>
	canEdit?: boolean
}>(), {
	canEdit: true,
})

const emit = defineEmits<{
	edit: []
	click: []
}>()

const typeLabels: Record<string, string> = {
	partnership: 'Partnership',
	single_parent: 'Single parent',
}

const name = computed(() => props.family.name || 'Unnamed family')
const typeLabel = computed(() =>
	props.family.type ? (typeLabels[props.family.type] ?? props.family.type) : '',
)
</script>

<template>
	<div
		class="family-card"
		role="button"
		tabindex="0"
		@click="emit('click')"
		@keydown.enter.prevent="emit('click')">
		<div class="family-card__icon" aria-hidden="true">
			<HumanMaleFemaleChild :size="28" />
		</div>

		<div class="family-card__body">
			<span v-if="typeLabel" class="family-card__badge">
				{{ typeLabel }}
			</span>

			<h3 class="family-card__name">
				{{ name }}
			</h3>

			<p v-if="family.description" class="family-card__meta">
				{{ family.description }}
			</p>
		</div>
	</div>
</template>

<style scoped>
.family-card {
	display: flex;
	align-items: flex-start;
	gap: 14px;
	height: 100%;
	padding: 14px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large, 8px);
	background-color: var(--color-main-background);
	cursor: pointer;
	transition: border-color .15s, box-shadow .15s;
}

.family-card:hover,
.family-card:focus-visible {
	border-color: var(--color-primary-element);
	box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
	outline: none;
}

.family-card__icon {
	display: flex;
	flex: 0 0 auto;
	align-items: center;
	justify-content: center;
	width: 48px;
	height: 48px;
	border-radius: var(--border-radius-large, 8px);
	background: var(--color-background-dark);
	color: var(--color-primary-element);
}

.family-card__body {
	display: flex;
	flex: 1 1 auto;
	flex-direction: column;
	gap: 6px;
	min-width: 0;
}

.family-card__badge {
	display: inline-block;
	width: fit-content;
	padding: 2px 8px;
	border-radius: var(--border-radius-pill);
	font-size: 0.72rem;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.03em;
	background: var(--color-background-dark);
	color: var(--color-text-maxcontrast);
}

.family-card__name {
	margin: 0;
	font-size: 1.05rem;
	font-weight: 600;
	line-height: 1.3;
	overflow: hidden;
	display: -webkit-box;
	-webkit-box-orient: vertical;
	-webkit-line-clamp: 2;
}

.family-card__meta {
	margin: 0;
	color: var(--color-text-maxcontrast);
	font-size: 0.88em;
	line-height: 1.4;
	overflow: hidden;
	display: -webkit-box;
	-webkit-box-orient: vertical;
	-webkit-line-clamp: 2;
}
</style>
