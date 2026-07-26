<script setup>
import { NcButton, NcAppSidebar, NcAppSidebarTab } from '@nextcloud/vue'

import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { usePeopleStore } from '@/stores/use-people-store.ts'
import { dateDisplay } from '@/utils/date.ts'
import Account from 'vue-material-design-icons/Account.vue'
import TimelineClock from 'vue-material-design-icons/TimelineClock.vue'
import CakeVariant from 'vue-material-design-icons/CakeVariant.vue'
import AccountGroup from 'vue-material-design-icons/AccountGroup.vue'
import InformationOutline from 'vue-material-design-icons/InformationOutline.vue'
import Cross from 'vue-material-design-icons/Cross.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import { LIVING_STATUSES } from '@/constants.ts'

const { selectedPerson } = storeToRefs(usePeopleStore())

const fullName = computed(() =>
	selectedPerson.value
		? [
			selectedPerson.value.firstName,
			selectedPerson.value.middleName,
			selectedPerson.value.lastName,
		].filter(Boolean).join(' ')
		: null,
)

const open = computed(() => selectedPerson.value !== null)
const status = computed(() => LIVING_STATUSES[selectedPerson.value?.livingStatus])
const birthLabel = computed(() => dateDisplay(selectedPerson.value?.birthDate))
const deathLabel = computed(() => dateDisplay(selectedPerson.value?.deathDate))
</script>

<template>
	<NcAppSidebar
		:open="open"
		:name="fullName"
		:subname="status"
		:background="selectedPerson?.avatar || 'https://placehold.co/600x300/e5e7eb/6b7280?text=Person'"
		no-toggle
		@close="selectedPerson = null">
		<NcAppSidebarTab
			id="overview"
			name="Overview">
			<template #icon>
				<InformationOutline :size="20" />
			</template>

			<div class="flex flex-col gap-4">
				<div class="flex gap-4">
					<Account :size="18" />
					<div>
						<label class="!font-bold">Gender</label>
						<div class="capitalize">
							{{ selectedPerson?.gender || 'Unknown' }}
						</div>
					</div>
				</div>

				<div class="flex gap-4 w-full">
					<CakeVariant :size="18" />

					<div class="flex-1">
						<div class="flex !justify-between items-center gap-2">
							<label class="!font-bold">Birth</label>
							<NcButton v-if="selectedPerson?.birthDate === null">
								<template #icon>
									<Plus :size="20" />
								</template>

								Add
							</NcButton>
						</div>
						<div v-if="birthLabel">
							{{ birthLabel }}
						</div>
					</div>
				</div>

				<div class="flex gap-4 w-full">
					<Cross :size="18" />

					<div class="flex-1">
						<div class="flex !justify-between items-center gap-2">
							<label class="!font-bold">Death</label>
							<NcButton v-if="selectedPerson?.deathDate === null">
								<template #icon>
									<Plus :size="20" />
								</template>

								Add
							</NcButton>
						</div>
						<div v-if="deathLabel">
							{{ deathLabel }}
						</div>
					</div>
				</div>
			</div>

			<div
				v-if="selectedPerson?.biography"
				class="section">
				<h3>Notes</h3>

				<p class="notes">
					{{ selectedPerson.biography }}
				</p>
			</div>
		</NcAppSidebarTab>

		<NcAppSidebarTab
			id="timeline"
			name="Timeline">
			<template #icon>
				<TimelineClock :size="20" />
			</template>
		</NcAppSidebarTab>

		<NcAppSidebarTab
			id="family"
			order="3"
			name="Family">
			<template #icon>
				<AccountGroup :size="20" />
			</template>
		</NcAppSidebarTab>
	</NcAppSidebar>
</template>
