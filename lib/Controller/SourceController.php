<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Exception\ValidationException;
use OCA\Kinship\Http\Requests\SourceRequest;
use OCA\Kinship\Service\CitationService;
use OCA\Kinship\Service\SourceService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class SourceController extends BaseApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private SourceService $service,
		private CitationService $citationService,
	) {
		parent::__construct($appName, $request);
	}

	public function index(): DataResponse {
		return new DataResponse(['sources' => $this->service->getAll()]);
	}

	public function show(int $id): DataResponse {
		return new DataResponse($this->service->get($id));
	}

	public function citations(int $id): DataResponse {
		return new DataResponse([
			'citations' => $this->citationService->getForSource($id)
		]);
	}

	public function create(array $data): DataResponse {
		try {
			$validated = $this->validate($data, SourceRequest::class);
		} catch (ValidationException $e) {
			return $this->validationErrorResponse($e);
		}

		return new DataResponse($this->service->create($validated), Http::STATUS_CREATED);
	}

	public function update(int $id, array $data): DataResponse {
		try {
			$validated = $this->validate($data, SourceRequest::class, true);
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
