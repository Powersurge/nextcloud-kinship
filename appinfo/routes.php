<?php

declare(strict_types=1);

return [
	'routes' => [
		// SPA shell
		[
			'name' => 'page#index',
			'url' => '/{path}',
			'verb' => 'GET',
			// Catch-all for the SPA shell, but do not swallow API routes so
			// GET /api/... resolves to its controller instead of the shell.
			'requirements' => ['path' => '^(?!api(?:/|$)).*'],
			'defaults' => ['path' => ''],
		],

		// People
		['name' => 'person#index', 'url' => '/api/v1/people', 'verb' => 'GET'],
		['name' => 'person#create', 'url' => '/api/v1/people', 'verb' => 'POST'],
		['name' => 'person#show', 'url' => '/api/v1/people/{id}', 'verb' => 'GET'],
		['name' => 'person#update', 'url' => '/api/v1/people/{id}', 'verb' => 'PUT'],
		['name' => 'person#delete', 'url' => '/api/v1/people/{id}', 'verb' => 'DELETE'],
		['name' => 'person_family#show', 'url' => '/api/v1/people/{id}/family', 'verb' => 'GET'],

		// Families (membership is derived from relationships)
		['name' => 'family#index', 'url' => '/api/v1/families', 'verb' => 'GET'],
		['name' => 'family#create', 'url' => '/api/v1/families', 'verb' => 'POST'],
		['name' => 'family#show', 'url' => '/api/v1/families/{id}', 'verb' => 'GET'],
		['name' => 'family#update', 'url' => '/api/v1/families/{id}', 'verb' => 'PUT'],
		['name' => 'family#delete', 'url' => '/api/v1/families/{id}', 'verb' => 'DELETE'],
		['name' => 'family#members', 'url' => '/api/v1/families/{id}/members', 'verb' => 'GET'],

		// Relationships (canonical stored + derived computed)
		['name' => 'relationship#index', 'url' => '/api/v1/relationships', 'verb' => 'GET'],
		['name' => 'relationship#create', 'url' => '/api/v1/relationships', 'verb' => 'POST'],
		['name' => 'relationship#show', 'url' => '/api/v1/relationships/{id}', 'verb' => 'GET'],
		['name' => 'relationship#update', 'url' => '/api/v1/relationships/{id}', 'verb' => 'PUT'],
		['name' => 'relationship#delete', 'url' => '/api/v1/relationships/{id}', 'verb' => 'DELETE'],
		['name' => 'relationship#personRelationships', 'url' => '/api/v1/people/{personId}/relationships', 'verb' => 'GET'],
		['name' => 'relationship#personDerived', 'url' => '/api/v1/people/{personId}/relationships/derived', 'verb' => 'GET'],

		// Events
		['name' => 'event#index', 'url' => '/api/v1/events', 'verb' => 'GET'],
		['name' => 'event#create', 'url' => '/api/v1/events', 'verb' => 'POST'],
		['name' => 'event#show', 'url' => '/api/v1/events/{id}', 'verb' => 'GET'],
		['name' => 'event#update', 'url' => '/api/v1/events/{id}', 'verb' => 'PUT'],
		['name' => 'event#delete', 'url' => '/api/v1/events/{id}', 'verb' => 'DELETE'],
		['name' => 'event#participants', 'url' => '/api/v1/events/{id}/participants', 'verb' => 'GET'],
		['name' => 'event#personEvents', 'url' => '/api/v1/people/{personId}/events', 'verb' => 'GET'],
		['name' => 'event#familyEvents', 'url' => '/api/v1/families/{familyId}/events', 'verb' => 'GET'],

		// Participants
		['name' => 'participant#create', 'url' => '/api/v1/participants', 'verb' => 'POST'],
		['name' => 'participant#delete', 'url' => '/api/v1/participants/{id}', 'verb' => 'DELETE'],
		['name' => 'participant#personParticipations', 'url' => '/api/v1/people/{personId}/participations', 'verb' => 'GET'],

		// Places
		['name' => 'place#index', 'url' => '/api/v1/places', 'verb' => 'GET'],
		['name' => 'place#create', 'url' => '/api/v1/places', 'verb' => 'POST'],
		['name' => 'place#lookup', 'url' => '/api/v1/places/lookup', 'verb' => 'GET'],
		['name' => 'place#reverse', 'url' => '/api/v1/places/reverse', 'verb' => 'GET'],
		['name' => 'place#resolve', 'url' => '/api/v1/places/resolve', 'verb' => 'POST'],
		['name' => 'place#show', 'url' => '/api/v1/places/{id}', 'verb' => 'GET'],
		['name' => 'place#update', 'url' => '/api/v1/places/{id}', 'verb' => 'PUT'],
		['name' => 'place#delete', 'url' => '/api/v1/places/{id}', 'verb' => 'DELETE'],

		// Facts (?subjectType=&subjectId=)
		['name' => 'fact#index', 'url' => '/api/v1/facts', 'verb' => 'GET'],
		['name' => 'fact#create', 'url' => '/api/v1/facts', 'verb' => 'POST'],
		['name' => 'fact#show', 'url' => '/api/v1/facts/{id}', 'verb' => 'GET'],
		['name' => 'fact#update', 'url' => '/api/v1/facts/{id}', 'verb' => 'PUT'],
		['name' => 'fact#delete', 'url' => '/api/v1/facts/{id}', 'verb' => 'DELETE'],

		// Sources
		['name' => 'source#index', 'url' => '/api/v1/sources', 'verb' => 'GET'],
		['name' => 'source#create', 'url' => '/api/v1/sources', 'verb' => 'POST'],
		['name' => 'source#show', 'url' => '/api/v1/sources/{id}', 'verb' => 'GET'],
		['name' => 'source#update', 'url' => '/api/v1/sources/{id}', 'verb' => 'PUT'],
		['name' => 'source#delete', 'url' => '/api/v1/sources/{id}', 'verb' => 'DELETE'],
		['name' => 'source#citations', 'url' => '/api/v1/sources/{id}/citations', 'verb' => 'GET'],

		// Citations (?subjectType=&subjectId=)
		['name' => 'citation#index', 'url' => '/api/v1/citations', 'verb' => 'GET'],
		['name' => 'citation#create', 'url' => '/api/v1/citations', 'verb' => 'POST'],
		['name' => 'citation#show', 'url' => '/api/v1/citations/{id}', 'verb' => 'GET'],
		['name' => 'citation#update', 'url' => '/api/v1/citations/{id}', 'verb' => 'PUT'],
		['name' => 'citation#delete', 'url' => '/api/v1/citations/{id}', 'verb' => 'DELETE'],

		// Claims
		['name' => 'claim#index', 'url' => '/api/v1/claims', 'verb' => 'GET'],
		['name' => 'claim#create', 'url' => '/api/v1/claims', 'verb' => 'POST'],
		['name' => 'claim#show', 'url' => '/api/v1/claims/{id}', 'verb' => 'GET'],
		['name' => 'claim#accept', 'url' => '/api/v1/claims/{id}/accept', 'verb' => 'POST'],
		['name' => 'claim#reject', 'url' => '/api/v1/claims/{id}/reject', 'verb' => 'POST'],
		['name' => 'claim#delete', 'url' => '/api/v1/claims/{id}', 'verb' => 'DELETE'],

		// Media references (?subjectType=&subjectId=&albumId=)
		['name' => 'media#index', 'url' => '/api/v1/media', 'verb' => 'GET'],
		['name' => 'media#create', 'url' => '/api/v1/media', 'verb' => 'POST'],
		['name' => 'media#upload', 'url' => '/api/v1/media/upload', 'verb' => 'POST'],
		['name' => 'media#show', 'url' => '/api/v1/media/{id}', 'verb' => 'GET'],
		['name' => 'media#update', 'url' => '/api/v1/media/{id}', 'verb' => 'PUT'],
		['name' => 'media#delete', 'url' => '/api/v1/media/{id}', 'verb' => 'DELETE'],

		// Albums (?ownerType=&ownerId=)
		['name' => 'album#index', 'url' => '/api/v1/albums', 'verb' => 'GET'],
		['name' => 'album#create', 'url' => '/api/v1/albums', 'verb' => 'POST'],
		['name' => 'album#show', 'url' => '/api/v1/albums/{id}', 'verb' => 'GET'],
		['name' => 'album#update', 'url' => '/api/v1/albums/{id}', 'verb' => 'PUT'],
		['name' => 'album#delete', 'url' => '/api/v1/albums/{id}', 'verb' => 'DELETE'],
		['name' => 'album#media', 'url' => '/api/v1/albums/{id}/media', 'verb' => 'GET'],

		// Custom event types (owner-only management UI is v0.2)
		['name' => 'custom_event#index', 'url' => '/api/v1/custom-events', 'verb' => 'GET'],
		['name' => 'custom_event#create', 'url' => '/api/v1/custom-events', 'verb' => 'POST'],
		['name' => 'custom_event#show', 'url' => '/api/v1/custom-events/{id}', 'verb' => 'GET'],
		['name' => 'custom_event#update', 'url' => '/api/v1/custom-events/{id}', 'verb' => 'PUT'],
		['name' => 'custom_event#delete', 'url' => '/api/v1/custom-events/{id}', 'verb' => 'DELETE'],

		// Permissions
		['name' => 'permission#index', 'url' => '/api/v1/permissions', 'verb' => 'GET'],
		['name' => 'permission#me', 'url' => '/api/v1/permissions/me', 'verb' => 'GET'],
		['name' => 'permission#create', 'url' => '/api/v1/permissions', 'verb' => 'POST'],
		['name' => 'permission#delete', 'url' => '/api/v1/permissions/{id}', 'verb' => 'DELETE'],
		['name' => 'permission#searchUsers', 'url' => '/api/v1/admin/users', 'verb' => 'GET'],

		// Admin settings
		['name' => 'settings#index', 'url' => '/api/v1/admin/settings', 'verb' => 'GET'],
		['name' => 'settings#update', 'url' => '/api/v1/admin/settings', 'verb' => 'PUT'],

		// Tree, import, timeline
		['name' => 'tree#show', 'url' => '/api/v1/tree/{personId}', 'verb' => 'GET'],
		['name' => 'import#gedcom', 'url' => '/api/v1/import/gedcom', 'verb' => 'POST'],
		['name' => 'timeline#person', 'url' => '/api/v1/timeline/person/{personId}', 'verb' => 'GET'],
	],
];
