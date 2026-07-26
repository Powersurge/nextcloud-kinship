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
import GraveStone from 'vue-material-design-icons/GraveStone.vue'
import TextAccount from 'vue-material-design-icons/TextAccount.vue'
import PencilOutline from 'vue-material-design-icons/PencilOutline.vue'
import Calendar from 'vue-material-design-icons/Calendar.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import { LIVING_STATUSES } from '@/constants.ts'
import TimelineItem from '@/components/TimelineItem.vue'

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

			<div class="flex flex-col gap-6">
				<div class="flex gap-4">
					<Account :size="20" />
					<div>
						<label class="!font-bold">Gender</label>
						<div class="capitalize">
							{{ selectedPerson?.gender || 'Unknown' }}
						</div>
					</div>
				</div>

				<div class="flex gap-4 w-full">
					<CakeVariant :size="20" />

					<div class="flex-1">
						<div class="flex !justify-between items-center gap-2">
							<label class="!font-bold">Birth</label>
							<NcButton v-if="selectedPerson?.birthDate === null" size="small">
								<template #icon>
									<Plus />
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
					<GraveStone :size="20" />

					<div class="flex-1">
						<div class="flex !justify-between items-center gap-2">
							<label class="!font-bold">Death</label>
							<NcButton v-if="selectedPerson?.deathDate === null" size="small">
								<template #icon>
									<Plus />
								</template>

								Add
							</NcButton>
						</div>
						<div v-if="deathLabel">
							{{ deathLabel }}
						</div>
					</div>
				</div>

				<div class="flex gap-4 w-full">
					<Calendar :size="20" />

					<div class="flex-1">
						<div class="flex !justify-between items-center gap-2">
							<label class="!font-bold">Events</label>
							<NcButton size="small">
								<template #icon>
									<Plus />
								</template>

								Add
							</NcButton>
						</div>
						<div>
							{{ selectedPerson?.biography }}
						</div>
					</div>
				</div>

				<div class="flex gap-4 w-full">
					<TextAccount :size="20" />

					<div class="flex-1">
						<div class="flex !justify-between items-center gap-2">
							<label class="!font-bold">Biography</label>
							<NcButton size="small">
								<template #icon>
									<PencilOutline />
								</template>
								Edit
							</NcButton>
						</div>
						<div>
							{{ selectedPerson?.biography }}
						</div>
					</div>
				</div>
			</div>
		</NcAppSidebarTab>

		<NcAppSidebarTab
			id="timeline"
			name="Timeline">
			<template #icon>
				<TimelineClock :size="20" />
			</template>

			<div class="flex flex-col gap-6">
				<TimelineItem v-for="(event, index) in selectedPerson?.events" :key="index" :event="event" />
			</div>
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
