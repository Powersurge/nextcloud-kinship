import { createRouter, createWebHistory } from 'vue-router'
import { generateUrl } from '@nextcloud/router'

const routes = [
	{
		path: '/',
		name: 'dashboard',
		component: () => import('../views/Dashboard.vue'),
	},
	{
		path: '/people',
		name: 'people',
		components: {
			default: () => import('../views/people/Index.vue'),
			sidebar: () => import('../components/people/PersonSidebar.vue'),
		},
	},
	{
		path: '/families',
		name: 'families',
		component: () => import('../views/family/Index.vue'),
	},
	{
		path: '/relationships',
		name: 'relationships',
		component: () => import('../views/Relationships.vue'),
	},
	{
		path: '/events',
		name: 'events',
		component: () => import('../views/event/Index.vue'),
	},
	{
		path: '/sources',
		name: 'sources',
		component: () => import('../views/Sources.vue'),
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
	history: createWebHistory(generateUrl('/apps/kinship')),
	routes,
})
