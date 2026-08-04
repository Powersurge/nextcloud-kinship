<script setup lang="ts">
import './styles/app.css'
import { NcContent, NcAppContent } from '@nextcloud/vue'
import Navigation from './components/ui/Navigation.vue'
import EventModal from '@/components/modals/EventModal.vue'
import FactModal from '@/components/modals/FactModal.vue'
import CitationModal from '@/components/modals/CitationModal.vue'
import AlbumModal from '@/components/modals/AlbumModal.vue'
import AttachMediaModal from '@/components/modals/AttachMediaModal.vue'
import { storeToRefs } from 'pinia'
import { useEventStore } from '@/stores/use-event-store.ts'
import { useFactStore } from '@/stores/use-fact-store.ts'
import { useCitationStore } from '@/stores/use-citation-store.ts'
import { useAlbumStore } from '@/stores/use-album-store.ts'
import { useMediaStore } from '@/stores/use-media-store.ts'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import { usePersonTimelineStore } from '@/stores/use-person-timeline-store.ts'
import { usePersonSidebarStore } from '@/stores/use-person-sidebar-store.ts'
import type { Citation } from '@/types'

const { showFormModal, eventForm } = storeToRefs(useEventStore())
const { showFormModal: showFactModal, factForm } = storeToRefs(useFactStore())
const { showFormModal: showCitationModal, citationForm } = storeToRefs(useCitationStore())
const { showFormModal: showAlbumModal, albumForm } = storeToRefs(useAlbumStore())
const { showAttachModal } = storeToRefs(useMediaStore())
const { refreshSelectedPerson } = usePeopleStore()
const { reloadForPerson } = usePersonTimelineStore()
const { loadFacts } = useFactStore()
const { upsertCitation } = useCitationStore()

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
}

async function onAlbumSaved() {
	await usePersonSidebarStore().reloadMedia()
}
</script>

<template>
	<NcContent app-name="kinship-app-navigation" class="content-styleguidist">
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
	</NcContent>
</template>
