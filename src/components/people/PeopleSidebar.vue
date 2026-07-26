<script setup lang="ts">
import IconMagnify from 'vue-material-design-icons/Magnify.vue'
import IconCogOutline from 'vue-material-design-icons/CogOutline.vue'
import IconShareVariantOutline from 'vue-material-design-icons/ShareVariantOutline.vue'
import { NcAppSidebar, NcAppSidebarTab } from '@nextcloud/vue'
import { usePeopleStore } from '../../stores/use-people-store.ts'
import {computed, ref} from 'vue'

const { selectedPerson, setSelectedPerson } = usePeopleStore()

const showSidebar = ref(selectedPerson != null)
</script>

<template>
	<NcAppSidebar
			v-model:open="showSidebar"
			@close="setSelectedPerson(null)"
			name="cat-picture.jpg"
			subname="last edited 3 weeks ago"
			background="https://nextcloud.com/wp-content/uploads/2022/08/nextcloud-logo-icon.svg">
			<NcAppSidebarTab id="search-tab" name="Search">
				<template #icon>
					<IconMagnify :size="20" />
				</template>
				Search tab content
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
</template>
