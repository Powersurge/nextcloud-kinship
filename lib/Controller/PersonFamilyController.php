<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Service\PersonFamilyService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class PersonFamilyController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private PersonFamilyService $service,
	) {
		parent::__construct(
			$appName,
			$request
		);
	}

	/**
	 * Family sidebar sections for a person (partnerships, children, parents).
	 */
	public function show(int $id): DataResponse {
		return new DataResponse(
			$this->service->getForPerson($id)
		);
	}
}
