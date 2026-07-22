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
				{{ event.eventDate }}
			</strong>

			<p>
				{{ event.title }}
			</p>

			<small>
				{{ event.type }}
			</small>
		</div>
	</div>
</template>

<script>

import { generateUrl } from '@nextcloud/router'

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

		async loadEvents() {

			const response = await fetch(
				generateUrl(
					`/apps/kinship/api/v1/timeline/person/${this.personId}`,
				),
			)

			this.events = await response.json()
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
