<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Service\ClaimService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ClaimController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private ClaimService $service,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * All claims, optionally filtered by ?status= or ?subjectType=&subjectId=
	 */
	public function index(?string $status = null, ?string $subjectType = null, ?int $subjectId = null): DataResponse {
		if ($subjectType !== null && $subjectId !== null) {
			$claims = $this->service->getForSubject($subjectType, $subjectId);
		} elseif ($status !== null) {
			$claims = $this->service->getByStatus($status);
		} else {
			$claims = $this->service->getAll();
		}

		return new DataResponse(['claims' => $claims]);
	}

	public function show(int $id): DataResponse {
		return new DataResponse($this->service->get($id));
	}

	public function create(array $data): DataResponse {
		return new DataResponse($this->service->create($data), Http::STATUS_CREATED);
	}

	public function accept(int $id): DataResponse {
		return new DataResponse($this->service->accept($id));
	}

	public function reject(int $id): DataResponse {
		return new DataResponse($this->service->reject($id));
	}

	public function delete(int $id): DataResponse {
		$this->service->delete($id);

		return new DataResponse(['deleted' => true]);
	}
}
