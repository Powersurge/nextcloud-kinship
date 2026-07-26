<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Service\CitationService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class CitationController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private CitationService $service,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Citations for a subject, e.g. ?subjectType=person&subjectId=5
	 */
	public function index(string $subjectType, int $subjectId): DataResponse {
		return new DataResponse([
			'citations' => $this->service->getForSubject($subjectType, $subjectId)
		]);
	}

	public function show(int $id): DataResponse {
		return new DataResponse($this->service->get($id));
	}

	public function create(array $data): DataResponse {
		return new DataResponse($this->service->create($data), Http::STATUS_CREATED);
	}

	public function update(int $id, array $data): DataResponse {
		return new DataResponse($this->service->update($id, $data));
	}

	public function delete(int $id): DataResponse {
		$this->service->delete($id);

		return new DataResponse(['deleted' => true]);
	}
}
