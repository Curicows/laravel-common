<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Http\Dtos;

use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

/**
 * @template TValue
 */
class ErrorDto extends Data
{
    public bool $error = true;

    public function __construct(
        public readonly string $message,
        public readonly int $statusCode,
        /** @var array<string, array<string>> $errors */
        public readonly array $errors = [],
        public readonly ?string $debugMessage = null,
        public readonly mixed $trace = null,
    ) {}

    public function toResponse($request): JsonResponse
    {
        $response = parent::toResponse($request);
        $response->setStatusCode($this->statusCode);

        return $response;
    }

    public function defaultWrap(): ?string
    {
        return null;
    }
}
