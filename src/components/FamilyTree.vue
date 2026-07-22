<template>
	<div class="tree">
		<div class="person-node">
			{{ person.firstName }}
			{{ person.lastName }}
		</div>

		<div
			v-if="children.length"
			class="children">
			<div
				v-for="child in children"
				:key="child.id"
				class="child">
				<div class="line" />

				<FamilyTree
					:person="child"
					:children="child.children || []" />
			</div>
		</div>
	</div>
</template>

<script>

export default {

	name: 'FamilyTree',

	props: {

		person: {
			type: Object,
			required: true,
		},

		children: {
			type: Array,
			default: () => [],
		},

	},

}

</script>

<style scoped>

.tree {
	text-align: center;
	margin: 20px;
}

.person-node {

	display: inline-block;

	background: #0082c9;

	color: white;

	padding: 10px 20px;

	border-radius: 8px;

}

.children {

	display: flex;

	justify-content: center;

	gap: 30px;

	margin-top: 30px;

}

.child {

	position: relative;

}

.line {

	height: 20px;

	border-left: 2px solid #888;

	margin: auto;

}

</style>
