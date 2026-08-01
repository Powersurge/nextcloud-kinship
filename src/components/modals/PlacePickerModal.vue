<script setup lang="ts">
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { NcButton, NcDialog, NcLoadingIcon, NcNoteCard } from '@nextcloud/vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { reverseGeocodePlace, type PlaceLookupOption } from '@/api/places.ts'

// Fix default marker icons when bundling with Vite
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'
import { debounce } from '@/utils/helpers.ts'

L.Icon.Default.mergeOptions({
	iconRetinaUrl: markerIcon2x,
	iconUrl: markerIcon,
	shadowUrl: markerShadow,
})

const props = defineProps<{
	open: boolean
	initialLatitude?: string | null
	initialLongitude?: string | null
}>()

const emit = defineEmits<{
	'update:open': [value: boolean]
	picked: [result: PlaceLookupOption]
}>()

const mapEl = ref<HTMLElement | null>(null)
const loading = ref(false)
const error = ref('')
const picked = ref<PlaceLookupOption | null>(null)

let map: L.Map | null = null
let marker: L.Marker | null = null

function close() {
	emit('update:open', false)
}

function destroyMap() {
	if (map) {
		map.remove()
		map = null
		marker = null
	}
}

const lookup = debounce(async (lat: number, lng: number) => {
	try {
		const result = await reverseGeocodePlace(String(lat), String(lng))
		picked.value = result ?? {
			value: `manual:${lat},${lng}`,
			id: null,
			label: `${lat.toFixed(5)}, ${lng.toFixed(5)}`,
			name: `${lat.toFixed(5)}, ${lng.toFixed(5)}`,
			fullName: `${lat.toFixed(5)}, ${lng.toFixed(5)}`,
			latitude: String(lat),
			longitude: String(lng),
			provider: null,
			providerPlaceId: null,
			source: 'manual',
		}
	} catch (e) {
		error.value = 'Could not look up that location. You can still confirm the coordinates.'
		picked.value = {
			value: `manual:${lat},${lng}`,
			id: null,
			label: `${lat.toFixed(5)}, ${lng.toFixed(5)}`,
			name: `${lat.toFixed(5)}, ${lng.toFixed(5)}`,
			fullName: `${lat.toFixed(5)}, ${lng.toFixed(5)}`,
			latitude: String(lat),
			longitude: String(lng),
			provider: null,
			providerPlaceId: null,
			source: 'manual',
		}
		console.error('Reverse geocode failed', e)
	} finally {
		loading.value = false
	}
}, 1000)

async function setPoint(lat: number, lng: number) {
	if (!map) {
		return
	}

	const latLng = L.latLng(lat, lng)
	if (marker) {
		marker.setLatLng(latLng)
	} else {
		marker = L.marker(latLng).addTo(map)
	}
	map.panTo(latLng)
	error.value = ''

	loading.value = true

	lookup(lat, lng)
}

async function initMap() {
	await nextTick()
	if (!mapEl.value) {
		return
	}

	destroyMap()

	const startLat = props.initialLatitude ? Number(props.initialLatitude) : 20
	const startLng = props.initialLongitude ? Number(props.initialLongitude) : 0
	const hasInitial = Number.isFinite(startLat) && Number.isFinite(startLng)
		&& props.initialLatitude != null && props.initialLongitude != null

	map = L.map(mapEl.value, {
		center: hasInitial ? [startLat, startLng] : [20, 0],
		zoom: hasInitial ? 12 : 2,
	})

	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: '&copy; OpenStreetMap contributors',
		maxZoom: 19,
	}).addTo(map)

	map.on('click', (event: L.LeafletMouseEvent) => {
		setPoint(event.latlng.lat, event.latlng.lng)
	})

	// Leaflet needs a resize after dialog animation
	setTimeout(() => map?.invalidateSize(), 100)

	if (hasInitial) {
		await setPoint(startLat, startLng)
	}
}

function confirm() {
	if (!picked.value) {
		return
	}
	emit('picked', picked.value)
	close()
}

watch(
	() => props.open,
	(open) => {
		if (open) {
			error.value = ''
			picked.value = null
			initMap()
		} else {
			destroyMap()
		}
	},
)

onBeforeUnmount(() => {
	destroyMap()
})
</script>

<template>
	<NcDialog
		:open="open"
		name="Pick a location"
		size="large"
		:close-on-click-outside="false"
		@update:open="emit('update:open', $event)">
		<div class="place-picker">
			<p class="place-picker__hint">
				Click the map to choose a location.
			</p>

			<div ref="mapEl" class="place-picker__map" />

			<NcNoteCard v-if="error" type="error">
				{{ error }}
			</NcNoteCard>

			<div v-if="loading" class="place-picker__status">
				<NcLoadingIcon :size="20" />
				<span>Looking up address…</span>
			</div>

			<div v-else-if="picked" class="place-picker__status">
				<strong>{{ picked.label }}</strong>
			</div>

			<div class="place-picker__actions">
				<NcButton type="button" @click="close">
					Cancel
				</NcButton>
				<NcButton
					type="button"
					variant="primary"
					:disabled="!picked || loading"
					@click="confirm">
					Use this location
				</NcButton>
			</div>
		</div>
	</NcDialog>
</template>

<style scoped>
.place-picker {
	display: flex;
	flex-direction: column;
	gap: 12px;
	padding: 4px 0 8px;
}

.place-picker__hint {
	margin: 0;
	color: var(--color-text-maxcontrast);
}

.place-picker__map {
	width: 100%;
	height: 360px;
	border-radius: var(--border-radius-large, 8px);
	overflow: hidden;
	border: 1px solid var(--color-border);
	z-index: 0;
}

.place-picker__status {
	display: flex;
	align-items: center;
	gap: 8px;
	min-height: 24px;
}

.place-picker__actions {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
}
</style>
