<script setup lang="ts">
import { NcButton, NcSelect } from '@nextcloud/vue'
import MapMarker from 'vue-material-design-icons/MapMarker.vue'
import { defineAsyncComponent, onMounted, ref, useAttrs } from 'vue'
import {
	lookupPlaces,
	resolvePlace,
	type PlaceLookupOption,
} from '@/api/places.ts'
import { debounce } from '@/utils/helpers.ts'

defineOptions({ inheritAttrs: false })

const SEARCH_DEBOUNCE_MS = 1500

const PlacePickerModal = defineAsyncComponent(
	() => import('../modals/PlacePickerModal.vue'),
)

const model = defineModel<number | null>()
const attrs = useAttrs()

const options = ref<PlaceLookupOption[]>([])
const selected = ref<PlaceLookupOption | null>(null)
const pickerOpen = ref(false)
const resolving = ref(false)

let searchRequestId = 0

onMounted(() => {
	loadSelected()
})

async function loadSelected() {
	const { results } = await lookupPlaces({
		selected: model.value ? [model.value] : [],
	})
	options.value = results
	selected.value = results.find((result) => result.id === model.value) ?? null
}

async function applyLookupResult(option: PlaceLookupOption | null) {
	if (!option) {
		selected.value = null
		model.value = null
		return
	}

	if (option.id) {
		selected.value = option
		model.value = option.id
		return
	}

	resolving.value = true
	try {
		const place = await resolvePlace({
			name: option.name,
			fullName: option.fullName,
			latitude: option.latitude,
			longitude: option.longitude,
			provider: option.provider,
			providerPlaceId: option.providerPlaceId,
		})

		const resolved: PlaceLookupOption = {
			value: place.id,
			label: place.fullName || place.name,
			id: place.id,
			name: place.name,
			fullName: place.fullName,
			latitude: place.latitude ?? option.latitude,
			longitude: place.longitude ?? option.longitude,
			provider: place.provider ?? option.provider,
			providerPlaceId: place.providerPlaceId ?? option.providerPlaceId,
			source: 'local',
		}

		selected.value = resolved
		model.value = place.id
		options.value = [
			resolved,
			...options.value.filter((item) => item.id !== place.id && item.value !== option.value),
		]
	} finally {
		resolving.value = false
	}
}

async function onSelect(option: PlaceLookupOption | null) {
	await applyLookupResult(option)
}

async function onPicked(result: PlaceLookupOption) {
	await applyLookupResult(result)
}

const onSearch = debounce(() => async (searchQuery: string, loading: (value: boolean) => void) => {
	loading(true)

	const requestId = ++searchRequestId

	try {
		const { results } = await lookupPlaces({
			searchQuery,
			selected: model.value ? [model.value] : [],
		})
		if (requestId === searchRequestId) {
			options.value = results
		}
	} finally {
		if (requestId === searchRequestId) {
			loading(false)
		}
	}
}, SEARCH_DEBOUNCE_MS)
</script>

<template>
	<div class="select-place">
		<div class="select-place__group">
			<NcSelect
				class="select-place__select"
				:model-value="selected"
				append-to-body
				input-label="Place"
				label="label"
				placeholder="Search for a place"
				:options="options"
				:filterable="false"
				:loading="resolving"
				v-bind="$attrs"
				@update:model-value="onSelect"
				@search="onSearch" />

			<NcButton
				class="select-place__picker-btn"
				type="button"
				variant="secondary"
				aria-label="Pick location on map"
				title="Pick location on map"
				:disabled="resolving || Boolean(attrs.disabled)"
				@click="pickerOpen = true">
				<template #icon>
					<MapMarker :size="20" />
				</template>

				Pick a location
			</NcButton>
		</div>

		<PlacePickerModal
			v-model:open="pickerOpen"
			:initial-latitude="selected?.latitude"
			:initial-longitude="selected?.longitude"
			@picked="onPicked" />
	</div>
</template>

<style scoped>
.select-place {
	width: 100%;
}

.select-place__group {
	display: flex;
	align-items: end;
	gap: 0.5rem;
	width: 100%;
}

.select-place__select {
	flex: 1 1 auto;
	min-width: 0;
}

.select-place__select :deep(.v-select) {
	border-top-right-radius: 0;
	border-bottom-right-radius: 0;
}

.select-place__picker-btn {
	height: 37px;
	margin-bottom: 4px;
}
</style>
