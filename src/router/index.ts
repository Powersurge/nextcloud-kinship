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
		path: '/people/:id',
		name: 'person-detail',
		component: () => import('../views/people/Detail.vue'),
		props: true,
	},
	{
		path: '/families',
		name: 'families',
		component: () => import('../views/family/Index.vue'),
	},
	{
		path: '/families/:id',
		name: 'family-detail',
		component: () => import('../views/family/Detail.vue'),
		props: true,
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
		path: '/events/:id',
		name: 'event-detail',
		component: () => import('../views/event/Detail.vue'),
		props: true,
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
