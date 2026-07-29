<script setup lang="ts">
import { NcSelect } from '@nextcloud/vue'
import { useOptionsStore } from '@/stores/use-options-store.ts'
import { storeToRefs } from 'pinia'
import { onMounted } from 'vue'
import type { SelectOption } from '@/types'

const model = defineModel<number | null>()

const { people } = storeToRefs(useOptionsStore())

onMounted(async () => {
	await useOptionsStore().loadPeople({
		selected: model.value ? [model.value] : [],
	})
})

const onSearch = async (searchQuery: string, loading: (value: boolean) => void) => {
	loading(true)
	await useOptionsStore().loadPeople({
		searchQuery,
	})
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
		:options="people"
		:filterable="false"
		:reduce="reduceOption"
		v-bind="$attrs"
		@search="onSearch" />
</template>
