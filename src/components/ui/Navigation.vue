<script setup lang="ts">
import { NcAppNavigation, NcAppNavigationItem } from '@nextcloud/vue'
import FamilyTree from 'vue-material-design-icons/FamilyTree.vue'
import Account from 'vue-material-design-icons/Account.vue'
import ViewDashboard from 'vue-material-design-icons/ViewDashboard.vue'
import HumanMaleFemaleChild from 'vue-material-design-icons/HumanMaleFemaleChild.vue'
import Calendar from 'vue-material-design-icons/Calendar.vue'
import VectorLink from 'vue-material-design-icons/VectorLink.vue'
</script>

<template>
	<NcAppNavigation>
		<template #list>
			<NcAppNavigationItem name="Dashboard" to="/">
				<template #icon>
					<ViewDashboard :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem name="People" to="/people">
				<template #icon>
					<Account :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem name="Families" to="/families">
				<template #icon>
					<HumanMaleFemaleChild :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem name="Relationships" to="/relationships">
				<template #icon>
					<VectorLink :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem name="Events" to="/events">
				<template #icon>
					<Calendar :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem name="Family Tree" to="/tree">
				<template #icon>
					<FamilyTree :size="20" />
				</template>
			</NcAppNavigationItem>
		</template>
	</NcAppNavigation>
</template>
