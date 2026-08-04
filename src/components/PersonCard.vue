<script setup lang="ts">
import { computed } from 'vue'
import { NcActions, NcActionButton } from '@nextcloud/vue'
import { dateDisplay } from '@/utils/date.ts'

import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Account from 'vue-material-design-icons/AccountCircle.vue'
import Cake from 'vue-material-design-icons/CakeVariant.vue'
import GraveStone from 'vue-material-design-icons/GraveStone.vue'
import { getLivingStatusLabel } from '@/utils/helpers.ts'

const props = defineProps<{
	person: Record<string, any>
}>()

const emit = defineEmits<{
	select: {}
	edit: []
	delete: []
}>()

const statusLabel = computed(() => getLivingStatusLabel(props.person.livingStatus))
const birthLabel = computed(() => dateDisplay(props.person.birthDate))
const deathLabel = computed(() => dateDisplay(props.person.deathDate))
</script>

<template>
	<div class="person-card">
		<div class="person-card__actions">
			<NcActions>
				<NcActionButton close-after-click @click="emit('edit')">
					<template #icon>
						<Pencil :size="20" />
					</template>
					Edit
				</NcActionButton>

				<NcActionButton close-after-click @click="emit('delete')">
					<template #icon>
						<Delete :size="20" />
					</template>
					Delete
				</NcActionButton>
			</NcActions>
		</div>

		<div class="person-card__avatar">
			<Account :size="56" />
		</div>

		<h3 class="person-card__name">
			{{ person.fullName }}
		</h3>

		<div
			v-if="person.preferredName"
			class="person-card__nickname">
			"{{ person.preferredName }}"
		</div>

		<div
			class="person-card__status"
			:class="person.livingStatus">
			{{ statusLabel }}
		</div>

		<div
			v-if="birthLabel || deathLabel"
			class="person-card__details">
			<div
				v-if="birthLabel"
				class="detail">
				<Cake :size="18" />
				<span>{{ birthLabel }}</span>
			</div>

			<div
				v-if="deathLabel"
				class="detail">
				<GraveStone :size="18" />
				<span>{{ deathLabel }}</span>
			</div>
		</div>
	</div>
</template>

<style scoped>
.person-card {
	position: relative;

	display: flex;
	flex-direction: column;
	align-items: center;

	padding: 20px 18px;

	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);

	background: var(--color-main-background);

	text-align: center;

	transition: box-shadow .15s,
	transform .15s,
	border-color .15s;
}

.person-card:hover {
	transform: translateY(-2px);

	border-color: var(--color-primary);

	box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
}

.person-card__actions {
	position: absolute;
	top: 10px;
	right: 10px;
}

.person-card__avatar {
	display: flex;
	align-items: center;
	justify-content: center;

	width: 84px;
	height: 84px;

	margin-bottom: 16px;

	border-radius: 50%;

	background: var(--color-background-hover);

	color: var(--color-primary);
}

.person-card__name {
	margin: 0;

	font-size: 1.05rem;
	font-weight: 600;

	line-height: 1.3;
}

.person-card__nickname {
	margin-top: 4px;

	color: var(--color-text-maxcontrast);

	font-style: italic;
	font-size: .9rem;
}

.person-card__status {
	margin-top: 14px;

	padding: 4px 12px;

	border-radius: 999px;

	background: rgba(46, 160, 67, .12);
	color: #2ea043;

	font-size: .78rem;
	font-weight: 600;
}

.person-card__status.deceased {
	background: rgba(140, 140, 140, .15);
	color: var(--color-text-maxcontrast);
}

.person-card__status.unknown {
	background: rgb(255, 238, 197);
	color: rgb(102, 71, 0);
}

.person-card__details {
	display: flex;
	flex-direction: column;
	gap: 10px;

	width: 100%;

	margin-top: 18px;
	padding-top: 16px;

	border-top: 1px solid var(--color-border);
}

.detail {
	display: flex;
	align-items: center;
	gap: 10px;

	color: var(--color-text-maxcontrast);

	font-size: .9rem;
}

.detail svg {
	flex-shrink: 0;
}
</style>
