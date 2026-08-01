<template>
	<div>
		<h3>
			Timeline
		</h3>

		<div
			v-for="event in events"
			:key="event.id"
			class="event">
			<strong>
				{{ dateDisplay(event.date) }}
			</strong>

			<p>
				{{ event.title || event.type }}
			</p>

			<small>
				{{ event.type }}
			</small>
		</div>
	</div>
</template>

<script>

import { dateDisplay } from '@/utils/date.ts'
import { fetchPersonTimeline } from '@/api/timeline.ts'

export default {

	name: 'PersonTimeline',

	props: {

		personId: {
			type: Number,
			required: true,
		},

	},

	data() {

		return {

			events: [],

		}

	},

	mounted() {

		this.loadEvents()

	},

	methods: {

		dateDisplay,

		async loadEvents() {
			this.events = await fetchPersonTimeline(this.personId)
		},

	},

}

</script>

<style scoped>

.event {

	border-left: 3px solid #0082c9;

	padding-left: 15px;

	margin: 15px;

}

</style>
