<script setup lang="ts">
import { NcSelect } from '@nextcloud/vue'
import { useOptionsStore } from '@/stores/use-options-store.ts'
import { storeToRefs } from 'pinia'
import { onMounted } from 'vue'
import type { SelectOption } from '@/types'

const model = defineModel<number | null>()

const { places } = storeToRefs(useOptionsStore())

onMounted(async () => {
	await useOptionsStore().loadPlaces()
})

const onSearch = async (search: string, loading: (value: boolean) => void) => {
	loading(true)
	await useOptionsStore().loadPlaces(search)
	loading(false)
}
const reduceOption = (option: SelectOption) => option.value
</script>

<template>
	<NcSelect
		v-model="model"
		input-label="Place"
		label="label"
		placeholder="Select place"
		:options="places"
		:reduce="reduceOption"
		v-bind="$attrs"
		@search="onSearch" />
</template>
