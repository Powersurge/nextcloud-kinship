<script setup lang="ts">
import { computed } from 'vue'
import { NcActionButton } from '@nextcloud/vue'
import BookOpenPageVariantOutline from 'vue-material-design-icons/BookOpenPageVariantOutline.vue'
import { useCitationStore } from '@/stores/use-citation-store.ts'

const props = defineProps<{
	subjectType: string
	subjectId: number
}>()

const citationStore = useCitationStore()

const label = computed(() => {
	const count = citationStore.citationCount(props.subjectType, props.subjectId)
	return count > 0 ? `Cite (${count})` : 'Cite'
})

function onClick() {
	citationStore.openCreateCitationForm(props.subjectType, props.subjectId)
}
</script>

<template>
	<NcActionButton close-after-click @click="onClick">
		<template #icon>
			<BookOpenPageVariantOutline :size="20" />
		</template>
		{{ label }}
	</NcActionButton>
</template>
