<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { type Node, type Edge, VueFlow } from '@vue-flow/core'
import { Background } from '@vue-flow/background'
import { Controls } from '@vue-flow/controls'
import { MiniMap } from '@vue-flow/minimap'

import '@vue-flow/core/dist/style.css'
import '@vue-flow/core/dist/theme-default.css'

import dagre from '@dagrejs/dagre'

import { NcEmptyContent, NcLoadingIcon, NcSelect } from '@nextcloud/vue'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import FamilyTreeIcon from 'vue-material-design-icons/FamilyTree.vue'
import LockOutline from 'vue-material-design-icons/LockOutline.vue'
import LockOpenOutline from 'vue-material-design-icons/LockOpenOutline.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Minus from 'vue-material-design-icons/Minus.vue'
import ArrowExpand from 'vue-material-design-icons/ArrowExpand.vue'
import MaleNode from '@/components/ui/flow-nodes/MaleNode.vue'
import FemaleNode from '@/components/ui/flow-nodes/FemaleNode.vue'

const root = ref<any>(null)
const loading = ref(true)

const people = ref([])
const rootId = ref<number | null>(null)

const nodes = ref<Node[]>([])
const edges = ref<Edge[]>([])

const graph = new dagre.graphlib.Graph()
graph.setDefaultEdgeLabel(() => ({}))

graph.setGraph({
	rankdir: 'TB',
	ranksep: 120,
	nodesep: 60,
})

function traverse(person: any, parentId?: number) {
	nodes.value.push({
		id: person.id.toString(),
		data: {
			label:
				`${person.firstName} ${person.lastName}`,
			person,
		},
		type: person.gender,
		position: { x: 0, y: 0 },
	})

	if (person.spouse) {
		const spouseId = `spouse-${person.spouse.id}`

		nodes.value.push({
			position: {
				x: 250,
				y: 0,
			},
			id: spouseId,
			type: person.spouse.gender,
			data: {
				label: `${person.spouse.firstName} ${person.spouse.lastName}`,
			},
		})

		edges.value.push({
			id: `${person.id}-${spouseId}`,
			source: String(person.id),
			target: spouseId,
			sourceHandle: `${person.id}-spouse`,
			targetHandle: `${spouseId}-spouse`,
			animated: true,
			type: 'default',
			style: {
				strokeDasharray: '5 5',
			},
			label: 'Spouse',
		})
	}

	if (parentId) {
		edges.value.push({
			id: `${parentId}-${person.id}`,
			source: String(parentId),
			target: String(person.id),
			sourceHandle: 'default',
			targetHandle: 'default',
			type: 'default',
		})
	}

	for (const child of person.children ?? []) {
		traverse(child, person.id)
	}
}

function layout() {
	nodes.value.forEach((n: any) => {
		graph.setNode(n.id, {
			width: 180,
			height: 60,
		})
	})

	edges.value.forEach((e: any) => {
		graph.setEdge(e.source, e.target)
	})

	dagre.layout(graph)

	nodes.value = nodes.value.map((node: any) => {
		const p = graph.node(node.id)

		return {
			...node,
			position: {
				x: p.x,
				y: p.y,
			},
		} satisfies Node
	})
}

function buildGraph(tree: any) {

	nodes.value = []
	edges.value = []

	traverse(tree)

	layout()
}

async function loadPeople() {
	const { data } = await axios.get(generateUrl('/apps/kinship/api/v1/people'))

	people.value = data.people.map((p: any) => ({
		value: p.id,
		label: `${p.firstName} ${p.lastName}`,
	}))

	if (!rootId.value && people.value.length) {
		rootId.value = people.value[0].value
	}
}

async function loadTree(id: number) {

	loading.value = true

	try {

		const { data } = await axios.get(
			generateUrl(`/apps/kinship/api/v1/tree/${id}`),
		)

		root.value = data

		buildGraph(data)

	} finally {
		loading.value = false
	}
}

watch(rootId, id => {
	if (id) {
		loadTree(id)
	}
})

onMounted(loadPeople)
</script>

<template>
	<div class="tree-view">
		<div class="bg-blue-200 w-fit !p-4 absolute right-0 z-10">
			<NcSelect
				v-model="rootId"
				:options="people"
				label="label"
				:reduce="o => o.value"
				input-label="Root person" />
		</div>

		<NcLoadingIcon
			v-if="loading"
			:size="32" />

		<NcEmptyContent
			v-else-if="!root"
			name="No tree">
			<template #icon>
				<FamilyTreeIcon />
			</template>
		</NcEmptyContent>

		<VueFlow
			v-else
			v-model:nodes="nodes"
			v-model:edges="edges"
			fit-view-on-init
			class="flow">
			<template #node-male="props">
				<MaleNode :id="props.id" :data="props.data" />
			</template>

			<template #node-female="props">
				<FemaleNode :id="props.id" :data="props.data" />
			</template>

			<Background />

			<MiniMap />

			<Controls>
				<template #icon-lock>
					<LockOutline size="20" fill-color="black" />
				</template>
				<template #icon-unlock>
					<LockOpenOutline size="20" fill-color="black" />
				</template>
				<template #icon-zoom-in>
					<Plus size="20" fill-color="black" />
				</template>
				<template #icon-zoom-out>
					<Minus size="20" fill-color="black" />
				</template>
				<template #icon-fit-view>
					<ArrowExpand size="20" fill-color="black" />
				</template>
			</Controls>
		</VueFlow>
	</div>
</template>

<style scoped>
.tree-view {
	height: 100%;
	display: flex;
	flex-direction: column;
}

.flow {
	flex: 1;
	height: 700px;
}
</style>
