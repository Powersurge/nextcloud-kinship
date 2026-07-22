<script setup lang="ts">
import { NcContent, NcAppContent } from '@nextcloud/vue'
import Navigation from './components/ui/Navigation.vue'
</script>

<template>
	<NcContent app-name="kinship-app-navigation" class="content-styleguidist">
		<Navigation />

		<NcAppContent>
			<RouterView />
		</NcAppContent>
	</NcContent>
</template>
