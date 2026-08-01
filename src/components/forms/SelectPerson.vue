<script setup lang="ts">
import { NcSelect } from '@nextcloud/vue'
import { fetchPeopleOptions } from '@/api/people.ts'
import { onMounted, ref } from 'vue'
import type { SelectOption } from '@/types'

const model = defineModel<number | null>()

const options = ref<SelectOption[]>([])

onMounted(async () => {
	options.value = await fetchPeopleOptions({
		selected: model.value ? [model.value] : [],
	})
})

const onSearch = async (searchQuery: string, loading: (value: boolean) => void) => {
	loading(true)
	options.value = await fetchPeopleOptions({ searchQuery })
	loading(false)
}

const reduceOption = (option: SelectOption) => option.value
</script>

<template>
	<NcSelect
		v-model="model"
		append-to-body
		input-label="Person"
		label="label"
		placeholder="--- Select a person ---"
		:options="options"
		:filterable="false"
		:reduce="reduceOption"
		v-bind="$attrs"
		@search="onSearch" />
</template>
