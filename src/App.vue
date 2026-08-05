<script setup lang="ts">
import './styles/app.css'
import { NcAppContent, NcContent, NcEmptyContent } from '@nextcloud/vue'
import LockOutline from 'vue-material-design-icons/LockOutline.vue'
import Navigation from './components/ui/Navigation.vue'
import EventModal from '@/components/modals/EventModal.vue'
import FactModal from '@/components/modals/FactModal.vue'
import CitationModal from '@/components/modals/CitationModal.vue'
import AlbumModal from '@/components/modals/AlbumModal.vue'
import AttachMediaModal from '@/components/modals/AttachMediaModal.vue'
import { storeToRefs } from 'pinia'
import { useRoute } from 'vue-router'
import { useEventStore } from '@/stores/use-event-store.ts'
import { useFactStore } from '@/stores/use-fact-store.ts'
import { useCitationStore } from '@/stores/use-citation-store.ts'
import { useAlbumStore } from '@/stores/use-album-store.ts'
import { useMediaStore } from '@/stores/use-media-store.ts'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import { usePersonTimelineStore } from '@/stores/use-person-timeline-store.ts'
import { usePersonSidebarStore } from '@/stores/use-person-sidebar-store.ts'
import { usePermissionsStore } from '@/stores/use-permissions-store.ts'
import type { Citation } from '@/types'

const route = useRoute()
const { canView } = storeToRefs(usePermissionsStore())
const { showFormModal, eventForm } = storeToRefs(useEventStore())
const { showFormModal: showFactModal, factForm } = storeToRefs(useFactStore())
const { showFormModal: showCitationModal, citationForm } = storeToRefs(useCitationStore())
const { showFormModal: showAlbumModal, albumForm } = storeToRefs(useAlbumStore())
const { showAttachModal, attachContext } = storeToRefs(useMediaStore())
const { refreshSelectedPerson } = usePeopleStore()
const { reloadForPerson } = usePersonTimelineStore()
const { loadFacts } = useFactStore()
const { upsertCitation } = useCitationStore()

// TODO: Check if can be refactored
function routeEntityId(): number | null {
	const value = Number(route.params.id)
	return Number.isFinite(value) && value > 0 ? value : null
}

async function reloadRouteMedia() {
	const id = routeEntityId()
	if (!id) {
		return
	}

	const mediaStore = useMediaStore()
	const albumStore = useAlbumStore()

	if (route.name === 'person-detail') {
		await Promise.all([
			mediaStore.loadForSubject('person', id),
			albumStore.loadForOwner('person', id),
		])
		return
	}

	if (route.name === 'family-detail') {
		await Promise.all([
			mediaStore.loadForSubject('family', id),
			albumStore.loadForOwner('family', id),
		])
		return
	}

	if (route.name === 'event-detail') {
		await Promise.all([
			mediaStore.loadForSubject('event', id),
			albumStore.loadForOwner('event', id),
		])
	}
}

async function reloadAttachContextMedia() {
	const context = attachContext.value
	if (!context?.subjectType || !context.subjectId) {
		await reloadRouteMedia()
		return
	}

	await Promise.all([
		useMediaStore().loadForSubject(context.subjectType, context.subjectId),
		useAlbumStore().loadForOwner(context.subjectType, context.subjectId),
	])
}

async function onEventSaved() {
	const personId = usePersonSidebarStore().selectedPersonId
	await refreshSelectedPerson()
	if (personId) {
		await reloadForPerson(personId)
		await usePersonSidebarStore().reloadCitations()
	}
}

async function onFactSaved() {
	const personId = usePersonSidebarStore().selectedPersonId
	if (personId) {
		await loadFacts('person', personId)
		await usePersonSidebarStore().reloadCitations()
	}
}

function onCitationSaved(citation: Record<string, unknown>) {
	upsertCitation(citation as Citation)
}

async function onMediaSaved() {
	await refreshSelectedPerson()
	await usePersonSidebarStore().reloadMedia()
	await reloadAttachContextMedia()
}

async function onAlbumSaved() {
	await usePersonSidebarStore().reloadMedia()
	await reloadRouteMedia()
}
</script>

<template>
	<NcContent app-name="kinship-app-navigation" class="content-styleguidist">
		<template v-if="canView">
			<Navigation />

			<NcAppContent>
				<RouterView />
			</NcAppContent>

			<RouterView name="sidebar" />

			<EventModal
				v-model:open="showFormModal"
				:event="eventForm"
				@saved="onEventSaved" />

			<FactModal
				v-model:open="showFactModal"
				:fact="factForm"
				@saved="onFactSaved" />

			<CitationModal
				v-model:open="showCitationModal"
				:citation="citationForm"
				@saved="onCitationSaved" />

			<AlbumModal
				v-model:open="showAlbumModal"
				:album="albumForm"
				@saved="onAlbumSaved" />

			<AttachMediaModal
				v-model:open="showAttachModal"
				@saved="onMediaSaved" />
		</template>

		<NcAppContent v-else>
			<NcEmptyContent
				class="kinship-no-access"
				name="Access required"
				description="You do not have permission to view Kinship. Ask a Nextcloud administrator to grant you a role in Settings → Administration → Kinship.">
				<template #icon>
					<LockOutline />
				</template>
			</NcEmptyContent>
		</NcAppContent>
	</NcContent>
</template>

<style scoped>
.kinship-no-access {
	margin: auto;
	padding: 48px 24px;
}
</style>
