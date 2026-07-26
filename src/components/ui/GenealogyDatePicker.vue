<script setup lang="ts">
import { NcInputField, NcSelect } from '@nextcloud/vue'
import { DATE_KIND_OPTIONS } from '../../constants.ts'
import type { GenologyDateType } from '../../types'
import { createEmptyGenologyDate } from '../../utils/date.ts'

const precisionOptions = [
	{ value: 'year', label: 'Year' },
	{ value: 'yearMonth', label: 'Year, Month' },
	{ value: 'yearMonthDay', label: 'Year, Month, Day' },
]

withDefaults(defineProps<{
	label: string,
}>(), {
	label: 'Date',
})

const dateModel = defineModel<GenologyDateType>({
	set(value) {
		if (!value) {
			return createEmptyGenologyDate()
		}
		return value
	},
	default: () => {
		return createEmptyGenologyDate()
	},
})
</script>

<template>
	<fieldset
		class="grid grid-cols-6 gap-x-4 gap-y-2 !p-4 bg-gray-100 rounded-lg [&>legend]:text-white [&>legend]:!px-2 [&>legend]:bg-gray-600">
		<legend class="rounded">
			{{ label }}
		</legend>
		<NcSelect
			v-model="dateModel.kind"
			class="!min-w-full col-span-3"
			input-label="Date Kind"
			:options="DATE_KIND_OPTIONS"
			:reduce="(option) => option.value" />

		<NcSelect
			v-model="dateModel.precision"
			class="!min-w-full col-span-3"
			input-label="Precision"
			:disabled="dateModel.kind === 'unknown'"
			:options="precisionOptions"
			:reduce="(option) => option.value" />

		<div v-if="dateModel.kind !== 'unknown'" class="col-span-full">
			<div
				:class="{'flex gap-2 justify-between' : dateModel.kind === 'between'}">
				<div
					class="grid gap-x-1 w-full"
					:class="{
						'grid-cols-1': dateModel.precision === 'year',
						'grid-cols-2': dateModel.precision === 'yearMonth',
						'grid-cols-3': dateModel.precision === 'yearMonthDay',
					}">
					<label
						v-if="dateModel.kind === 'between'"
						class="col-span-full relative after:absolute after:h-px after:w-full after:border after:border-gray-200 overflow-clip after:border-dashed after:top-1/2 after:left-10"
						for="start_year">
						Start
					</label>

					<NcInputField
						id="start_year"
						v-model="dateModel.start.year"
						:class="{'cols-span-full': dateModel.precision === 'year'}"
						min="1"
						step="1"
						label="Year" />

					<NcInputField
						v-if="['yearMonth', 'yearMonthDay'].includes(dateModel.precision)"
						v-model="dateModel.start.month"
						type="number"
						min="1"
						max="12"
						step="1"
						label="Month" />

					<NcInputField
						v-if="dateModel.precision === 'yearMonthDay'"
						v-model="dateModel.start.day"
						type="number"
						min="1"
						max="31"
						step="1"
						label="Day" />
				</div>

				<div
					v-if="dateModel.kind === 'between'"
					class="grid gap-x-1 w-full"
					:class="{
						'grid-cols-1': dateModel.precision === 'year',
						'grid-cols-2': dateModel.precision === 'yearMonth',
						'grid-cols-3': dateModel.precision === 'yearMonthDay',
					}">
					<label class="col-span-full" for="end_year">End</label>

					<NcInputField
						id="end_year"
						v-model="dateModel.end.year"
						:class="{'cols-span-full': dateModel.precision === 'year'}"
						min="1"
						step="1"
						label="Year" />

					<NcInputField
						v-if="['yearMonth', 'yearMonthDay'].includes(dateModel.precision)"
						v-model="dateModel.end.month"
						type="number"
						min="1"
						max="12"
						step="1"
						label="Month" />

					<NcInputField
						v-if="dateModel.precision === 'yearMonthDay'"
						v-model="dateModel.end.day"
						type="number"
						min="1"
						max="31"
						step="1"
						label="Day" />
				</div>
			</div>
		</div>
	</fieldset>
</template>
