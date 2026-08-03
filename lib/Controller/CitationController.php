<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Exception\ValidationException;
use OCA\Kinship\Http\Requests\CitationRequest;
use OCA\Kinship\Service\CitationService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class CitationController extends BaseApiController {
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
		try {
			$validated = $this->validate($data, CitationRequest::class);
		} catch (ValidationException $e) {
			return $this->validationErrorResponse($e);
		}

		return new DataResponse($this->service->create($validated), Http::STATUS_CREATED);
	}

	public function update(int $id, array $data): DataResponse {
		try {
			$validated = $this->validate($data, CitationRequest::class, true);
		} catch (ValidationException $e) {
			return $this->validationErrorResponse($e);
		}

		return new DataResponse($this->service->update($id, $validated));
	}

	public function delete(int $id): DataResponse {
		$this->service->delete($id);

		return new DataResponse(['deleted' => true]);
	}
}
