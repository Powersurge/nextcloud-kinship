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

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { dateDisplay } from '../utils/date'

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

			const response = await axios.get(
				generateUrl(
					`/apps/kinship/api/v1/timeline/person/${this.personId}`,
				),
			)

			this.events = response.data
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
