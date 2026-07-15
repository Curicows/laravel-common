<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases;

use Curicows\LaravelCommon\Http\Dtos\ErrorDto;
use Exception;
use Throwable;

abstract class BaseException extends Exception
{
    private array $errors = [];

    private bool $report = true;

    public function __construct(string $message = '', int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function report(): bool
    {
        return $this->report;
    }

    public function render(): ErrorDto
    {
        return ErrorDto::from([
            'message' => $this->getMessage(),
            'statusCode' => $this->getCode(),
            'errors' => $this->errors,
        ]);
    }
}
