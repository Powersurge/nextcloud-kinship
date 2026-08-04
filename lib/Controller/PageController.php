<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\AppInfo\Application;
use OCA\Viewer\Event\LoadViewer;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IRequest;

/**
 * @psalm-suppress UnusedClass
 */
class PageController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private IEventDispatcher $eventDispatcher,
	) {
		parent::__construct($appName, $request);
	}

	#[NoCSRFRequired]
	#[NoAdminRequired]
	#[OpenAPI(OpenAPI::SCOPE_IGNORE)]
	#[FrontpageRoute(verb: 'GET', url: '/')]
	public function index(): TemplateResponse {
		// Make OCA.Viewer available for media slideshow / file preview.
		if (class_exists(LoadViewer::class)) {
			$this->eventDispatcher->dispatchTyped(new LoadViewer());
		}

		return new TemplateResponse(
			Application::APP_ID,
			'index',
		);
	}
}
