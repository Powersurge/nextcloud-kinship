<?php

declare(strict_types=1);

namespace OCA\Kinship\Controller;

use OCA\Kinship\Exception\ValidationException;
use OCA\Kinship\Http\Requests\FormRequest;
use OCA\Kinship\Validation\RequestValidator;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\Server;

/**
 * Shared API controller helpers (request validation, error responses).
 */
abstract class BaseApiController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @param class-string<FormRequest> $formRequest
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 * @throws ValidationException
	 */
	protected function validate(array $data, string $formRequest, bool $partial = false): array {
		return Server::get(RequestValidator::class)->validate($data, $formRequest, $partial);
	}

	protected function validationErrorResponse(ValidationException $e): DataResponse {
		return new DataResponse([
			'error' => $e->getMessage(),
			'errors' => $e->getErrors(),
		], Http::STATUS_UNPROCESSABLE_ENTITY);
	}
}
