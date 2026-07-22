<template>
	<div class="timeline-item">
		<strong>
			{{ event.eventDate }}
		</strong>

		<div>
			{{ event.title }}
		</div>

		<small>
			{{ event.type }}
		</small>
	</div>
</template>

<script>
export default {
	name: 'TimelineItem',

	props: {
		event: {
			type: Object,
			required: true,
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
