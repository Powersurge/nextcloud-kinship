<?php

declare(strict_types=1);

return [
	'routes' => [
		[
			'name' => 'page#index',
			'url' => '/',
			'verb' => 'GET',
		],
		[
			'name' => 'person#index',
			'url' => '/api/v1/people',
			'verb' => 'GET',
		],
		[
			'name' => 'person#create',
			'url' => '/api/v1/people',
			'verb' => 'POST',
		],
		[
			'name' => 'person#show',
			'url' => '/api/v1/people/{id}',
			'verb' => 'GET',
		],
		[
			'name' => 'person#update',
			'url' => '/api/v1/people/{id}',
			'verb' => 'PUT',
		],
		[
			'name' => 'person#delete',
			'url' => '/api/v1/people/{id}',
			'verb' => 'DELETE',
		],
		[
			'name' => 'family#index',
			'url' => '/api/v1/families',
			'verb' => 'GET',
		],
		[
			'name' => 'family#show',
			'url' => '/api/v1/families/{id}',
			'verb' => 'GET',
		],
		[
			'name' => 'family#create',
			'url' => '/api/v1/families',
			'verb' => 'POST',
		],
		[
			'name' => 'family#update',
			'url' => '/api/v1/families/{id}',
			'verb' => 'PUT',
		],
		[
			'name' => 'family#delete',
			'url' => '/api/v1/families/{id}',
			'verb' => 'DELETE',
		],
		[
			'name' => 'relationship#index',
			'url' => '/api/v1/relationships',
			'verb' => 'GET',
		],
		[
			'name' => 'relationship#show',
			'url' => '/api/v1/relationships/{id}',
			'verb' => 'GET',
		],
		[
			'name' => 'relationship#personRelationships',
			'url' => '/api/v1/people/{personId}/relationships',
			'verb' => 'GET',
		],
		[
			'name' => 'event#index',
			'url' => '/api/v1/events',
			'verb' => 'GET',
		],
		[
			'name' => 'event#show',
			'url' => '/api/v1/events/{id}',
			'verb' => 'GET',
		],
		[
			'name' => 'event#create',
			'url' => '/api/v1/events',
			'verb' => 'POST',
		],
		[
			'name' => 'event#update',
			'url' => '/api/v1/events/{id}',
			'verb' => 'PUT',
		],
		[
			'name' => 'event#delete',
			'url' => '/api/v1/events/{id}',
			'verb' => 'DELETE',
		],
		[
			'name' => 'event#personEvents',
			'url' => '/api/v1/people/{personId}/events',
			'verb' => 'GET',
		],
		[
			'name' => 'event#familyEvents',
			'url' => '/api/v1/families/{familyId}/events',
			'verb' => 'GET',
		],
		[
			'name' => 'tree#show',
			'url' => '/api/v1/tree/{personId}',
			'verb' => 'GET',
		],
		[
			'name' => 'import#gedcom',
			'url' => '/api/v1/import/gedcom',
			'verb' => 'POST',
		],
		[
			'name' => 'timeline#person',
			'url' => '/api/v1/timeline/person/{personId}',
			'verb' => 'GET',
		],
	],
];
