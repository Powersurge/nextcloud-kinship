<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { NcLoadingIcon } from '@nextcloud/vue'
import { storeToRefs } from 'pinia'
import type { Person } from '@/types'
import { useFamilyDetailStore } from '@/stores/use-family-detail-store.ts'

const familyStore = useFamilyDetailStore()
const { parents, children, natalBundles, loading } = storeToRefs(familyStore)

const hasHousehold = computed(() => parents.value.length > 0 || children.value.length > 0)

function personName(person: Person): string {
	return person.fullName
		|| `${person.firstName ?? ''} ${person.lastName ?? ''}`.trim()
		|| `#${person.id}`
}
</script>

<template>
	<div class="members-tree">
		<NcLoadingIcon v-if="loading" :size="24" />

		<template v-else>
			<section class="members-tree__section">
				<h3 class="members-tree__title">
					Spouses and Children
				</h3>

				<div v-if="hasHousehold" class="members-tree__household">
					<ul v-if="parents.length" class="members-tree__row">
						<li
							v-for="person in parents"
							:key="`parent-${person.id}`"
							class="members-tree__person members-tree__person--parent">
							<RouterLink
								:to="{ name: 'person-detail', params: { id: String(person.id) } }"
								class="members-tree__link">
								{{ personName(person) }}
							</RouterLink>
							<span class="members-tree__role">Spouse</span>
						</li>
					</ul>
					<p v-else class="members-tree__empty">
						No spouses recorded for this family
					</p>

					<div class="members-tree__branch">
						<h4 class="members-tree__subtitle">
							Children
						</h4>
						<ul v-if="children.length" class="members-tree__list">
							<li
								v-for="person in children"
								:key="`child-${person.id}`"
								class="members-tree__person">
								<RouterLink
									:to="{ name: 'person-detail', params: { id: String(person.id) } }"
									class="members-tree__link">
									{{ personName(person) }}
								</RouterLink>
							</li>
						</ul>
						<p v-else class="members-tree__empty">
							No children recorded
						</p>
					</div>
				</div>
				<p v-else class="members-tree__empty">
					No members recorded for this family
				</p>
			</section>

			<section class="members-tree__section">
				<h3 class="members-tree__title">
					Parents and Siblings
				</h3>

				<div v-if="natalBundles.length" class="members-tree__natal">
					<article
						v-for="bundle in natalBundles"
						:key="`natal-${bundle.person.id}`"
						class="members-tree__natal-card">
						<h4 class="members-tree__subtitle">
							<RouterLink
								:to="{ name: 'person-detail', params: { id: String(bundle.person.id) } }"
								class="members-tree__link">
								{{ personName(bundle.person) }}
							</RouterLink>
						</h4>

						<div class="members-tree__branch">
							<p class="members-tree__label">
								Parents
							</p>
							<ul v-if="bundle.parents.length" class="members-tree__list">
								<li
									v-for="person in bundle.parents"
									:key="`p-${bundle.person.id}-${person.id}`"
									class="members-tree__person">
									<RouterLink
										:to="{ name: 'person-detail', params: { id: String(person.id) } }"
										class="members-tree__link">
										{{ personName(person) }}
									</RouterLink>
								</li>
							</ul>
							<p v-else class="members-tree__empty">
								No parents recorded
							</p>
						</div>

						<div class="members-tree__branch">
							<p class="members-tree__label">
								Siblings
							</p>
							<ul v-if="bundle.siblings.length" class="members-tree__list">
								<li
									v-for="person in bundle.siblings"
									:key="`s-${bundle.person.id}-${person.id}`"
									class="members-tree__person">
									<RouterLink
										:to="{ name: 'person-detail', params: { id: String(person.id) } }"
										class="members-tree__link">
										{{ personName(person) }}
									</RouterLink>
								</li>
							</ul>
							<p v-else class="members-tree__empty">
								No siblings recorded
							</p>
						</div>
					</article>
				</div>
				<p v-else class="members-tree__empty">
					Add spouses to see their parents and siblings
				</p>
			</section>
		</template>
	</div>
</template>

<style scoped>
.members-tree {
	display: flex;
	flex-direction: column;
	gap: 1.75rem;
}

.members-tree__section {
	display: flex;
	flex-direction: column;
	gap: 0.85rem;
}

.members-tree__title {
	margin: 0;
	font-size: 1.05rem;
	font-weight: 700;
}

.members-tree__subtitle {
	margin: 0;
	font-size: 0.95rem;
	font-weight: 700;
}

.members-tree__household,
.members-tree__natal {
	display: flex;
	flex-direction: column;
	gap: 1rem;
}

.members-tree__row {
	list-style: none;
	margin: 0;
	padding: 0;
	display: flex;
	flex-wrap: wrap;
	gap: 0.65rem;
}

.members-tree__branch {
	margin-inline-start: 0.75rem;
	padding-inline-start: 0.85rem;
	border-inline-start: 2px solid var(--color-border);
	display: flex;
	flex-direction: column;
	gap: 0.5rem;
}

.members-tree__list {
	list-style: none;
	margin: 0;
	padding: 0;
	display: flex;
	flex-direction: column;
	gap: 0.4rem;
}

.members-tree__person {
	display: flex;
	align-items: center;
	gap: 0.5rem;
	flex-wrap: wrap;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	padding: 0.55rem 0.75rem;
	background: var(--color-main-background);
}

.members-tree__person--parent {
	background: var(--color-background-dark);
}

.members-tree__link {
	font-weight: 600;
	color: var(--color-main-text);
	text-decoration: none;
}

.members-tree__link:hover {
	color: var(--color-primary-element);
	text-decoration: underline;
}

.members-tree__role {
	font-size: 0.75rem;
	font-weight: 600;
	padding: 2px 8px;
	border-radius: var(--border-radius-pill);
	background: var(--color-primary-element-light);
}

.members-tree__natal-card {
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	padding: 0.85rem 1rem;
	display: flex;
	flex-direction: column;
	gap: 0.85rem;
}

.members-tree__label {
	margin: 0;
	font-size: 0.8rem;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.03em;
	color: var(--color-text-maxcontrast);
}

.members-tree__empty {
	margin: 0;
	color: var(--color-text-maxcontrast);
	font-size: 0.9rem;
	font-style: italic;
}
</style>
