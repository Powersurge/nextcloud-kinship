export { API_BASE, apiUrl } from './client'

export {
	fetchPeople,
	fetchPerson,
	createPerson,
	updatePerson,
	deletePerson,
	fetchPersonFamily,
	fetchPeopleOptions,
} from './people'
export type { PeopleQueryParams } from './people'

export {
	fetchEvents,
	createEvent,
	updateEvent,
	deleteEvent,
	fetchPersonEvents,
} from './events'
export type { PersonEventsParams } from './events'

export {
	fetchFamilies,
	createFamily,
	updateFamily,
	deleteFamily,
} from './families'

export {
	fetchRelationships,
	createRelationship,
	updateRelationship,
	deleteRelationship,
} from './relationships'

export {
	fetchFacts,
	createFact,
	updateFact,
	deleteFact,
} from './facts'
export type { FactsQueryParams } from './facts'

export {
	fetchSources,
	fetchSource,
	createSource,
	updateSource,
	deleteSource,
	fetchSourceCitations,
} from './sources'

export {
	fetchCitations,
	createCitation,
	updateCitation,
	deleteCitation,
} from './citations'
export type { CitationsQueryParams } from './citations'

export {
	fetchPlaces,
	fetchPlacesOptions,
	lookupPlaces,
	reverseGeocodePlace,
	resolvePlace,
	createPlace,
} from './places'
export type { PlacesQueryParams, PlaceLookupOption } from './places'

export { fetchTree } from './tree'
export { importGedcom } from './import'
export type { GedcomImportResult } from './import'

export { updateAdminSettings } from './settings'
export type { AdminSettings } from './settings'

export { fetchPersonTimeline } from './timeline'
