<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Service\TreeService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class TreeController extends ApiController {

	public function __construct(
		string $appName,
		IRequest $request,
		private TreeService $service,
	) {

		parent::__construct(
			$appName,
			$request
		);

	}

	public function show(
		int $personId,
	): DataResponse {

		return new DataResponse(
			$this->service->getTree(
				$personId
			)
		);

	}

}
