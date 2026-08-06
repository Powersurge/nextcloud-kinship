<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { NcActions, NcActionButton } from '@nextcloud/vue'
import { dateDisplay } from '@/utils/date.ts'
import { mediaPreviewUrl } from '@/utils/mediaPreview.ts'

import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Account from 'vue-material-design-icons/AccountCircle.vue'
import OpenInNew from 'vue-material-design-icons/OpenInNew.vue'
import { getLivingStatusLabel } from '@/utils/helpers.ts'

const props = withDefaults(defineProps<{
	person: Record<string, any>
	canEdit?: boolean
	canDelete?: boolean
}>(), {
	canEdit: true,
	canDelete: true,
})

const emit = defineEmits<{
	select: {}
	edit: []
	delete: []
}>()

const statusLabel = computed(() => getLivingStatusLabel(props.person.livingStatus))
const birthLabel = computed(() => dateDisplay(props.person.birthDate))
const deathLabel = computed(() => dateDisplay(props.person.deathDate))
const lifespan = computed(() => {
	if (birthLabel.value && deathLabel.value) {
		return `${birthLabel.value} – ${deathLabel.value}`
	}
	if (birthLabel.value) {
		return `b. ${birthLabel.value}`
	}
	if (deathLabel.value) {
		return `d. ${deathLabel.value}`
	}
	return ''
})
const photoUrl = computed(() => {
	const fileId = props.person.photoFileId
	return typeof fileId === 'number' && fileId > 0
		? mediaPreviewUrl(fileId, 168)
		: null
})

const detailTo = computed(() => ({
	name: 'person-detail',
	params: { id: String(props.person.id) },
}))
</script>

<template>
	<div class="person-card" role="button" tabindex="0">
		<div class="person-card__avatar">
			<img
				v-if="photoUrl"
				:src="photoUrl"
				:alt="person.fullName || 'Person photo'"
				class="person-card__photo">
			<Account v-else :size="36" />
		</div>

		<div class="person-card__body">
			<div class="person-card__top">
				<div class="person-card__identity">
					<h3 class="person-card__name">
						{{ person.fullName }}
					</h3>
					<span
						v-if="person.preferredName"
						class="person-card__nickname">
						“{{ person.preferredName }}”
					</span>
				</div>

				<div v-if="canEdit || canDelete" class="person-card__actions" @click.stop>
					<NcActions>
						<NcActionButton v-if="canEdit" close-after-click @click="emit('edit')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Edit
						</NcActionButton>

						<NcActionButton v-if="canDelete" close-after-click @click="emit('delete')">
							<template #icon>
								<Delete :size="20" />
							</template>
							Delete
						</NcActionButton>
					</NcActions>
				</div>
			</div>

			<div class="person-card__meta">
				<span
					class="person-card__status"
					:class="person.livingStatus">
					{{ statusLabel }}
				</span>
				<span v-if="lifespan" class="person-card__lifespan">
					{{ lifespan }}
				</span>
			</div>

			<RouterLink
				v-if="person.id"
				:to="detailTo"
				class="person-card__detail-link"
				@click.stop>
				View details
				<OpenInNew :size="14" />
			</RouterLink>
		</div>
	</div>
</template>

<style scoped>
.person-card {
	display: flex;
	align-items: stretch;
	gap: 14px;
	height: 100%;
	padding: 14px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	background: var(--color-main-background);
	cursor: pointer;
	transition: border-color .15s, box-shadow .15s, background-color .15s;
}

.person-card:hover,
.person-card:focus-visible {
	border-color: var(--color-primary-element);
	box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
	outline: none;
}

.person-card__avatar {
	display: flex;
	flex: 0 0 auto;
	align-items: center;
	justify-content: center;
	width: 64px;
	height: 64px;
	border-radius: 50%;
	background: var(--color-background-dark);
	color: var(--color-primary-element);
	overflow: hidden;
}

.person-card__photo {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.person-card__body {
	display: flex;
	flex: 1 1 auto;
	flex-direction: column;
	gap: 8px;
	min-width: 0;
}

.person-card__top {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 8px;
}

.person-card__identity {
	min-width: 0;
}

.person-card__name {
	margin: 0;
	font-size: 1.05rem;
	font-weight: 600;
	line-height: 1.3;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.person-card__nickname {
	display: block;
	margin-top: 2px;
	color: var(--color-text-maxcontrast);
	font-style: italic;
	font-size: .85rem;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.person-card__actions {
	flex: 0 0 auto;
	margin: -6px -6px 0 0;
}

.person-card__meta {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: 8px;
}

.person-card__status {
	display: inline-flex;
	align-items: center;
	padding: 2px 8px;
	border-radius: var(--border-radius-pill, 999px);
	background: rgba(46, 160, 67, .12);
	color: #2ea043;
	font-size: .72rem;
	font-weight: 600;
	line-height: 1.4;
}

.person-card__status.deceased {
	background: rgba(140, 140, 140, .15);
	color: var(--color-text-maxcontrast);
}

.person-card__status.unknown {
	background: rgb(255, 238, 197);
	color: rgb(102, 71, 0);
}

.person-card__lifespan {
	color: var(--color-text-maxcontrast);
	font-size: .85rem;
}

.person-card__detail-link {
	display: inline-flex;
	align-items: center;
	gap: 0.3rem;
	margin-top: auto;
	width: fit-content;
	color: var(--color-primary-element);
	font-size: 0.85rem;
	font-weight: 600;
	text-decoration: none;
}

.person-card__detail-link:hover {
	text-decoration: underline;
}
</style>
