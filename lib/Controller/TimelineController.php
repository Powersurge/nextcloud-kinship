<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Db\EventMapper;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class TimelineController extends ApiController {

	public function __construct(
		string $appName,
		IRequest $request,
		private EventMapper $events,
	) {

		parent::__construct(
			$appName,
			$request
		);

	}

	public function person(
		int $personId,
	): DataResponse {

		return new DataResponse(
			$this->events
				->findByPerson(
					$personId
				)
		);

	}

}
