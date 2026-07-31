<script setup lang="ts">
import { NcSelect } from '@nextcloud/vue'
import { fetchPlacesOptions } from '@/api/places'
import { onMounted, ref } from 'vue'
import type { SelectOption } from '@/types'

const model = defineModel<number | null>()

const options = ref<SelectOption[]>([])

onMounted(async () => {
	options.value = await fetchPlacesOptions({
		selected: model.value ? [model.value] : [],
	})
})

const onSearch = async (searchQuery: string, loading: (value: boolean) => void) => {
	loading(true)
	options.value = await fetchPlacesOptions({ searchQuery })
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
		:options="options"
		:reduce="reduceOption"
		v-bind="$attrs"
		@search="onSearch" />
</template>
