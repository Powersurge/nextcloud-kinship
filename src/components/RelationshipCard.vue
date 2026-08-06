<script setup lang="ts">
import { computed } from 'vue'
import { NcActions, NcActionButton } from '@nextcloud/vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Heart from 'vue-material-design-icons/Heart.vue'
import AccountChildOutline from 'vue-material-design-icons/AccountChildOutline.vue'
import {
	isPartnershipType,
	relationshipTypeLabel,
} from '@/utils/relationships.ts'
import { dateDisplay } from '@/utils/date.ts'
import { mediaPreviewUrl } from '@/utils/mediaPreview.ts'

const props = withDefaults(defineProps<{
	relationship: Record<string, any>
	aName: string
	bName: string
	aPhotoFileId?: number | null
	bPhotoFileId?: number | null
	canEdit?: boolean
	canDelete?: boolean
}>(), {
	aPhotoFileId: null,
	bPhotoFileId: null,
	canEdit: true,
	canDelete: true,
})

const emit = defineEmits<{
	edit: []
	delete: []
}>()

const typeLabel = computed(() => relationshipTypeLabel(props.relationship.type))
const when = computed(() => dateDisplay(props.relationship.date))
const isPartnership = computed(() => isPartnershipType(props.relationship.type))

function initials(name: string): string {
	const parts = name.trim().split(/\s+/).filter(Boolean)
	if (parts.length === 0) {
		return '?'
	}
	if (parts.length === 1) {
		return parts[0].slice(0, 2).toUpperCase()
	}
	return `${parts[0][0] ?? ''}${parts[parts.length - 1][0] ?? ''}`.toUpperCase()
}

function firstName(name: string): string {
	return name.trim().split(/\s+/).filter(Boolean)[0] || name
}

function photoUrl(fileId: number | null | undefined): string | null {
	return typeof fileId === 'number' && fileId > 0
		? mediaPreviewUrl(fileId, 128)
		: null
}

const aInitials = computed(() => initials(props.aName))
const bInitials = computed(() => initials(props.bName))
const aFirst = computed(() => firstName(props.aName))
const bFirst = computed(() => firstName(props.bName))
const aPhoto = computed(() => photoUrl(props.aPhotoFileId))
const bPhoto = computed(() => photoUrl(props.bPhotoFileId))
</script>

<template>
	<article
		class="rel-card"
		:class="isPartnership ? 'rel-card--partnership' : 'rel-card--parent'">
		<div class="rel-card__actions" @click.stop>
			<NcActions v-if="canEdit || canDelete">
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

		<div class="rel-card__diagram" :aria-label="`${aName} ${typeLabel} ${bName}`">
			<div class="rel-card__person">
				<div class="rel-card__avatar">
					<img
						v-if="aPhoto"
						:src="aPhoto"
						:alt="aName"
						class="rel-card__photo">
					<span v-else>{{ aInitials }}</span>
				</div>
				<span class="rel-card__firstname">{{ aFirst }}</span>
				<span
					v-if="aName !== aFirst"
					class="rel-card__fullname"
					:title="aName">
					{{ aName }}
				</span>
			</div>

			<div class="rel-card__link">
				<span class="rel-card__rail" aria-hidden="true" />
				<span class="rel-card__pill">
					<Heart v-if="isPartnership" :size="14" />
					<AccountChildOutline v-else :size="14" />
					{{ typeLabel }}
				</span>
				<span class="rel-card__rail" aria-hidden="true" />
			</div>

			<div class="rel-card__person">
				<div class="rel-card__avatar">
					<img
						v-if="bPhoto"
						:src="bPhoto"
						:alt="bName"
						class="rel-card__photo">
					<span v-else>{{ bInitials }}</span>
				</div>
				<span class="rel-card__firstname">{{ bFirst }}</span>
				<span
					v-if="bName !== bFirst"
					class="rel-card__fullname"
					:title="bName">
					{{ bName }}
				</span>
			</div>
		</div>

		<footer v-if="when || relationship.note" class="rel-card__footer">
			<span v-if="when" class="rel-card__date">{{ when }}</span>
			<span v-if="relationship.note" class="rel-card__note">{{ relationship.note }}</span>
		</footer>
	</article>
</template>

<style scoped>
.rel-card {
	position: relative;
	display: flex;
	flex-direction: column;
	height: 100%;
	overflow: hidden;
	border: 1px solid var(--color-border);
	border-radius: calc(var(--border-radius-large, 8px) + 4px);
	background:
		radial-gradient(120% 90% at 50% 0%, var(--rel-tint, transparent) 0%, transparent 70%),
		var(--color-main-background);
	transition: border-color .18s, transform .18s, box-shadow .18s;
}

.rel-card--partnership {
	--rel-tint: rgba(232, 93, 117, .12);
	--rel-accent: #e85d75;
}

.rel-card--parent {
	--rel-tint: rgba(0, 119, 204, .10);
	--rel-accent: #0077cc;
}

.rel-card:hover {
	border-color: var(--rel-accent);
	transform: translateY(-2px);
	box-shadow: 0 8px 22px rgba(0, 0, 0, .08);
}

.rel-card__actions {
	position: absolute;
	top: 8px;
	right: 8px;
	z-index: 2;
}

.rel-card__diagram {
	display: grid;
	grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
	align-items: start;
	gap: 6px;
	padding: 28px 16px 18px;
}

.rel-card__person {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 8px;
	min-width: 0;
	text-align: center;
}

.rel-card__avatar {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 72px;
	height: 72px;
	border-radius: 50%;
	border: 3px solid var(--color-main-background);
	background: var(--color-background-dark);
	color: var(--rel-accent);
	font-size: 1rem;
	font-weight: 700;
	letter-spacing: 0.02em;
	box-shadow: 0 0 0 1px color-mix(in srgb, var(--rel-accent) 35%, transparent);
	overflow: hidden;
}

.rel-card__photo {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.rel-card__firstname {
	max-width: 100%;
	font-size: 1.05rem;
	font-weight: 700;
	line-height: 1.2;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.rel-card__fullname {
	max-width: 100%;
	margin-top: -4px;
	color: var(--color-text-maxcontrast);
	font-size: 0.75rem;
	line-height: 1.3;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.rel-card__link {
	display: flex;
	align-items: center;
	align-self: center;
	gap: 0;
	min-width: 88px;
	padding-top: 22px;
}

.rel-card__rail {
	flex: 1 1 auto;
	height: 2px;
	min-width: 12px;
	background: linear-gradient(
		90deg,
		transparent,
		color-mix(in srgb, var(--rel-accent) 70%, var(--color-border))
	);
}

.rel-card__rail:last-child {
	background: linear-gradient(
		90deg,
		color-mix(in srgb, var(--rel-accent) 70%, var(--color-border)),
		transparent
	);
}

.rel-card__pill {
	display: inline-flex;
	flex: 0 0 auto;
	align-items: center;
	gap: 4px;
	padding: 5px 10px;
	border-radius: 999px;
	background: color-mix(in srgb, var(--rel-accent) 16%, var(--color-main-background));
	color: var(--rel-accent);
	font-size: 0.72rem;
	font-weight: 700;
	letter-spacing: 0.02em;
	text-transform: uppercase;
	white-space: nowrap;
	box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--rel-accent) 25%, transparent);
}

.rel-card__footer {
	display: flex;
	flex-direction: column;
	gap: 4px;
	margin-top: auto;
	padding: 10px 16px 14px;
	border-top: 1px solid color-mix(in srgb, var(--color-border) 80%, transparent);
	color: var(--color-text-maxcontrast);
	font-size: 0.85rem;
	line-height: 1.4;
	text-align: center;
}

.rel-card__date {
	font-weight: 600;
	color: var(--color-main-text);
}

.rel-card__note {
	overflow: hidden;
	display: -webkit-box;
	-webkit-box-orient: vertical;
	-webkit-line-clamp: 2;
}
</style>
