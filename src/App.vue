<script setup lang="ts">
import './styles/app.css'
import { NcContent, NcAppContent, NcAppSidebar, NcAppSidebarTab } from '@nextcloud/vue'
import Navigation from './components/ui/Navigation.vue'
import IconCogOutline from 'vue-material-design-icons/CogOutline.vue'
import IconMagnify from 'vue-material-design-icons/Magnify.vue'
import IconShareVariantOutline from 'vue-material-design-icons/ShareVariantOutline.vue'
import { computed, ref } from 'vue'
import { usePeopleStore } from './stores/use-people-store.ts'
import { storeToRefs } from 'pinia'
const { selectedPerson } = storeToRefs(usePeopleStore())

const open = computed(() => selectedPerson.value != null)
</script>

<template>
	<NcContent app-name="kinship-app-navigation" class="content-styleguidist">
		<Navigation />

		<NcAppContent>
			<RouterView />
		</NcAppContent>

		<NcAppSidebar
			:open="open"
			no-toggle
			:name="`${selectedPerson?.firstName} ${selectedPerson?.lastName}`"
			subname="last edited 3 weeks ago"
			background="https://nextcloud.com/wp-content/uploads/2022/08/nextcloud-logo-icon.svg"
			@close="selectedPerson = null">
			<NcAppSidebarTab id="search-tab" name="Search">
				<template #icon>
					<IconMagnify :size="20" />
				</template>
				{{ selectedPerson?.firstName }}
			</NcAppSidebarTab>
			<NcAppSidebarTab id="settings-tab" name="Settings">
				<template #icon>
					<IconCogOutline :size="20" />
				</template>
				Settings tab content
			</NcAppSidebarTab>
			<NcAppSidebarTab id="share-tab" name="Sharing">
				<template #icon>
					<IconShareVariantOutline :size="20" />
				</template>
				Sharing tab content
			</NcAppSidebarTab>
		</NcAppSidebar>
	</NcContent>
</template>
