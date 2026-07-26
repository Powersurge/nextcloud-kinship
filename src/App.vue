<script setup lang="ts">
import './styles/app.css'
import { NcContent, NcAppContent } from '@nextcloud/vue'
import Navigation from './components/ui/Navigation.vue'
</script>

<template>
	<NcContent app-name="kinship-app-navigation" class="content-styleguidist">
		<Navigation />

		<NcAppContent>
			<RouterView />
		</NcAppContent>

		<RouterView name="sidebar" />
	</NcContent>
</template>
