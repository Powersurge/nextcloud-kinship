import { createRouter, createWebHashHistory } from 'vue-router'

const routes = [
	{
		path: '/',
		name: 'dashboard',
		component: () => import('../views/Dashboard.vue'),
	},
	{
		path: '/people',
		name: 'people',
		component: () => import('../views/People.vue'),
	},
	{
		path: '/families',
		name: 'families',
		component: () => import('../views/Families.vue'),
	},
	{
		path: '/events',
		name: 'events',
		component: () => import('../views/Events.vue'),
	},
	{
		path: '/tree',
		name: 'family-tree',
		component: () => import('../views/FamilyTree.vue'),
	},
	{
		path: '/import',
		name: 'import',
		component: () => import('../views/Import.vue'),
	},
]

export default createRouter({
	history: createWebHashHistory(),
	routes,
})
