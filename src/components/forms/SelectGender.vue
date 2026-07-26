<script setup lang="ts">
import { NcSelect } from '@nextcloud/vue'

const model = defineModel<String | null>({
	required: false,
})

const genderOptions = [
	{ value: 'male', label: 'Male' },
	{ value: 'female', label: 'Female' },
]
</script>

<template>
	<NcSelect
		v-model="model"
		input-label="Gender"
		placeholder="-- Select gender --"
		:options="genderOptions"
		:reduce="(option) => option.value"
		:clearable="false"
		v-bind="$attrs" />
</template>
