<script setup lang="ts">
import { NcSelect } from '@nextcloud/vue'

const model = defineModel<String | null>({
	required: false,
})

const livingStatusOptions = [
	{ value: 'living', label: 'Living' },
	{ value: 'deceased', label: 'Deceased' },
	{ value: 'unknown', label: 'Unknown' },
]
</script>

<template>
	<NcSelect
		v-model="model"
		input-label="Living status"
		placeholder="-- Select living status --"
		:options="livingStatusOptions"
		:reduce="(option) => option.value"
		:clearable="false"
		v-bind="$attrs" />
</template>
