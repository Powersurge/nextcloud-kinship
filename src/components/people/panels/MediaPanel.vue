<script setup lang="ts">
import { NcEmptyContent } from '@nextcloud/vue'
import ImageMultiple from 'vue-material-design-icons/ImageMultiple.vue'
import { storeToRefs } from 'pinia'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import MediaSection from '@/components/media/MediaSection.vue'

const { selectedPerson } = storeToRefs(usePeopleStore())
</script>

<template>
	<div class="media-tab">
		<NcEmptyContent
			v-if="!selectedPerson?.id"
			name="No person selected"
			description="Open a person to browse albums and media.">
			<template #icon>
				<ImageMultiple :size="20" />
			</template>
		</NcEmptyContent>

		<MediaSection
			v-else
			subject-type="person"
			:subject-id="selectedPerson.id"
			:show-primary-action="true" />
	</div>
</template>

<style scoped>
.media-tab {
	padding: 0.25rem 0 0.75rem;
}
</style>
