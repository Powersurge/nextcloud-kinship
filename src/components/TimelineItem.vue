<template>
	<div class="timeline-item">
		<strong>
			{{ when }}
		</strong>

		<div>
			{{ heading }}
		</div>

		<small>
			{{ event.type }}
		</small>
	</div>
</template>

<script>
import { dateDisplay } from '../utils/date'

export default {
	name: 'TimelineItem',

	props: {
		event: {
			type: Object,
			required: true,
		},
	},

	computed: {
		when() {
			return dateDisplay(this.event.date)
		},
		heading() {
			return this.event.title || this.event.type
		},
	},
}
</script>

<style scoped>
.timeline-item {
	border-left: 3px solid #0082c9;
	padding-left: 15px;
	margin: 15px 0;
}
</style>
