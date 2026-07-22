<?php

declare(strict_types=1);

namespace OCA\Kinship\AppInfo;

use OCA\Kinship\Db\EventMapper;
use OCA\Kinship\Db\FamilyMapper;
use OCA\Kinship\Db\PersonMapper;
use OCA\Kinship\Db\RelationshipMapper;
use OCA\Kinship\Import\GedcomExtractor;
use OCA\Kinship\Import\GedcomFamilyImporter;
use OCA\Kinship\Import\GedcomImporter;
use OCA\Kinship\Import\GedcomParser;
use OCA\Kinship\Import\GedcomRelationshipImporter;
use OCA\Kinship\Service\EventService;
use OCA\Kinship\Service\FamilyService;
use OCA\Kinship\Service\PersonService;
use OCA\Kinship\Service\RelationshipService;
use OCA\Kinship\Service\TreeService;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IDBConnection;

class Application extends App implements IBootstrap {
	public const APP_ID = 'kinship';

	/** @psalm-suppress PossiblyUnusedMethod */
	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerService(
			PersonMapper::class,
			function ($c) {
				return new PersonMapper(
					$c->get(IDBConnection::class)
				);
			}
		);

		$context->registerService(
			PersonService::class,
			function ($c) {
				return new PersonService(
					$c->get(PersonMapper::class)
				);
			}
		);

		$context->registerService(
			FamilyMapper::class,
			function ($c) {
				return new FamilyMapper(
					$c->get(\OCP\IDBConnection::class)
				);
			}
		);

		$context->registerService(
			FamilyService::class,
			function ($c) {
				return new FamilyService(
					$c->get(FamilyMapper::class)
				);
			}
		);

		$context->registerService(
			RelationshipMapper::class,
			function ($c) {
				return new RelationshipMapper(
					$c->get(\OCP\IDBConnection::class)
				);
			}
		);

		$context->registerService(
			RelationshipService::class,
			function ($c) {
				return new RelationshipService(
					$c->get(RelationshipMapper::class)
				);
			}
		);

		$context->registerService(
			EventMapper::class,
			function ($c) {
				return new EventMapper(
					$c->get(\OCP\IDBConnection::class)
				);
			}
		);

		$context->registerService(
			EventService::class,
			function ($c) {
				return new EventService(
					$c->get(EventMapper::class)
				);
			}
		);

		$context->registerService(
			TreeService::class,
			function ($c) {

				return new TreeService(
					$c->get(
						\OCA\Kinship\Db\PersonMapper::class
					),

					$c->get(
						\OCA\Kinship\Db\RelationshipMapper::class
					)
				);
			}
		);

		$context->registerService(
			GedcomParser::class,
			function () {
				return new GedcomParser();
			}
		);

		$context->registerService(
			GedcomImporter::class,
			function ($c) {

				return new GedcomImporter(
					$c->get(
						GedcomParser::class
					),

					$c->get(
						\OCA\Kinship\Service\PersonService::class
					)
				);
			}
		);

		$context->registerService(
			GedcomExtractor::class,
			function () {

				return new GedcomExtractor();
			}
		);

		$context->registerService(
			GedcomFamilyImporter::class,
			function ($c) {

				return new GedcomFamilyImporter(
					$c->get(
						\OCA\Kinship\Db\FamilyMapper::class
					)
				);
			}
		);

		$context->registerService(
			GedcomRelationshipImporter::class,
			function ($c) {

				return new GedcomRelationshipImporter(
					$c->get(
						\OCA\Kinship\Db\RelationshipMapper::class
					)
				);
			}
		);
	}

	public function boot(IBootContext $context): void {
	}
}
