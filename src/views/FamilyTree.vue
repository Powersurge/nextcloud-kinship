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
import FamilyTreeIcon from 'vue-material-design-icons/FamilyTree.vue'
import { fetchPeople } from '@/api/people.ts'
import { fetchTree } from '@/api/tree.ts'
import LockOutline from 'vue-material-design-icons/LockOutline.vue'
import LockOpenOutline from 'vue-material-design-icons/LockOpenOutline.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Minus from 'vue-material-design-icons/Minus.vue'
import ArrowExpand from 'vue-material-design-icons/ArrowExpand.vue'
import MaleNode from '@/components/ui/flow-nodes/MaleNode.vue'
import FemaleNode from '@/components/ui/flow-nodes/FemaleNode.vue'
import type { TreeNode, TreeSpouseSummary } from '@/utils/relationships.ts'

const root = ref<TreeNode | null>(null)
const loading = ref(true)

const people = ref<Array<{ value: number, label: string }>>([])
const rootId = ref<number | null>(null)

const nodes = ref<Node[]>([])
const edges = ref<Edge[]>([])

const NODE_WIDTH = 160
const NODE_HEIGHT = 56
const SPOUSE_GAP = 24

const graph = new dagre.graphlib.Graph()
graph.setDefaultEdgeLabel(() => ({}))

graph.setGraph({
	rankdir: 'TB',
	ranksep: 100,
	nodesep: 40,
	edgesep: 40,
})

interface SpousePair {
	personId: string
	spouseId: string
	index: number
}

const spousePairs = ref<SpousePair[]>([])
const spouseCountByPerson = ref<Map<string, number>>(new Map())
const addedSpousePersonIds = ref<Set<number>>(new Set())

function isSpouseNode(id: string) {
	return id.startsWith('spouse-')
}

function getSpouses(person: TreeNode): TreeSpouseSummary[] {
	if (person.spouses?.length) {
		return person.spouses
	}
	if (person.spouse) {
		return [person.spouse]
	}
	return []
}

function spouseEdgeStyle(status: string): Record<string, string | number> {
	if (status === 'separated') {
		return { strokeDasharray: '8 4', strokeWidth: 2 }
	}
	if (status === 'engaged') {
		return { strokeDasharray: '2 4', strokeWidth: 2 }
	}
	return { strokeDasharray: '6 4', strokeWidth: 2 }
}

function ensureSpouseNode(spouse: TreeSpouseSummary) {
	const spouseNodeId = `spouse-${spouse.id}`

	if (addedSpousePersonIds.value.has(spouse.id)) {
		return spouseNodeId
	}

	addedSpousePersonIds.value.add(spouse.id)
	nodes.value.push({
		id: spouseNodeId,
		type: spouse.gender,
		data: {
			label: `${spouse.firstName} ${spouse.lastName}`,
		},
		position: { x: 0, y: 0 },
	})

	return spouseNodeId
}

function traverse(person: TreeNode, parentId?: number) {
	const personId = String(person.id)

	nodes.value.push({
		id: personId,
		data: {
			label: `${person.firstName} ${person.lastName}`,
			person,
		},
		type: person.gender,
		position: { x: 0, y: 0 },
	})

	const spouses = getSpouses(person)
	const currentCount = spouseCountByPerson.value.get(personId) ?? 0
	spouseCountByPerson.value.set(personId, Math.max(currentCount, spouses.length))

	spouses.forEach((spouse, index) => {
		const spouseNodeId = ensureSpouseNode(spouse)

		spousePairs.value.push({ personId, spouseId: spouseNodeId, index })

		edges.value.push({
			id: `${personId}-${spouseNodeId}-${index}`,
			source: personId,
			target: spouseNodeId,
			sourceHandle: `${personId}-spouse-right`,
			targetHandle: `${spouseNodeId}-spouse-left`,
			type: 'straight',
			style: spouseEdgeStyle(spouse.status),
		})
	})

	if (parentId) {
		edges.value.push({
			id: `${parentId}-${personId}`,
			source: String(parentId),
			target: personId,
			sourceHandle: 'default',
			targetHandle: 'default',
			type: 'default',
		})
	}

	for (const child of person.children ?? []) {
		traverse(child, person.id)
	}
}

function coupleWidth(spouseCount: number): number {
	if (spouseCount <= 0) {
		return NODE_WIDTH
	}
	return NODE_WIDTH + spouseCount * (NODE_WIDTH + SPOUSE_GAP)
}

function layout() {
	graph.setGraph({
		rankdir: 'TB',
		ranksep: 100,
		nodesep: 40,
		edgesep: 40,
	})

	nodes.value.forEach((n) => {
		if (isSpouseNode(n.id)) {
			return
		}

		const spouseCount = spouseCountByPerson.value.get(n.id) ?? 0
		graph.setNode(n.id, {
			width: coupleWidth(spouseCount),
			height: NODE_HEIGHT,
		})
	})

	edges.value.forEach((e) => {
		if (e.sourceHandle?.includes('spouse')) {
			return
		}

		graph.setEdge(e.source, e.target)
	})

	dagre.layout(graph)

	const positions = new Map<string, { x: number, y: number }>()

	nodes.value = nodes.value.map((node: Node) => {
		if (isSpouseNode(node.id)) {
			return node
		}

		const p = graph.node(node.id)
		const spouseCount = spouseCountByPerson.value.get(node.id) ?? 0
		const extraWidth = spouseCount > 0 ? spouseCount * (NODE_WIDTH + SPOUSE_GAP) : 0
		const coupleOffset = extraWidth / 2
		const position = {
			x: p.x - NODE_WIDTH / 2 - coupleOffset,
			y: p.y - NODE_HEIGHT / 2,
		}

		positions.set(node.id, position)

		return { ...node, position }
	})

	for (const { personId, spouseId, index } of spousePairs.value) {
		const partnerPos = positions.get(personId)
		if (!partnerPos) {
			continue
		}

		const spouseIndex = nodes.value.findIndex(n => n.id === spouseId)
		if (spouseIndex === -1) {
			continue
		}

		nodes.value[spouseIndex] = {
			...nodes.value[spouseIndex],
			position: {
				x: partnerPos.x + (index + 1) * (NODE_WIDTH + SPOUSE_GAP),
				y: partnerPos.y,
			},
		}
	}
}

function buildGraph(tree: TreeNode) {
	nodes.value = []
	edges.value = []
	spousePairs.value = []
	spouseCountByPerson.value = new Map()
	addedSpousePersonIds.value = new Set()

	traverse(tree)
	layout()
}

async function loadPeople() {
	const list = await fetchPeople()

	people.value = list.map((p) => ({
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
		const data = await fetchTree(id)

		root.value = data as TreeNode
		buildGraph(data as TreeNode)
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
					<LockOutline :size="20" fill-color="black" />
				</template>
				<template #icon-unlock>
					<LockOpenOutline :size="20" fill-color="black" />
				</template>
				<template #icon-zoom-in>
					<Plus :size="20" fill-color="black" />
				</template>
				<template #icon-zoom-out>
					<Minus :size="20" fill-color="black" />
				</template>
				<template #icon-fit-view>
					<ArrowExpand :size="20" fill-color="black" />
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
