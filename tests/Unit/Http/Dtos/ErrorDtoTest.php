<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Http\Dtos;

use Curicows\LaravelCommon\Http\Dtos\ErrorDto;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\LaravelData\Data;

#[CoversClass(ErrorDto::class)]
class ErrorDtoTest extends TestCase
{
    public function test_construct_sets_all_properties(): void
    {
        $message = 'Test error message';
        $statusCode = 400;
        $errors = ['field1' => 'Error 1', 'field2' => 'Error 2'];

        $dto = new ErrorDto($message, $statusCode, $errors);

        $this->assertSame($message, $dto->message);
        $this->assertSame($statusCode, $dto->statusCode);
        $this->assertSame($errors, $dto->errors);
    }

    public function test_construct_with_default_empty_errors(): void
    {
        $message = 'Test error message';
        $statusCode = 500;

        $dto = new ErrorDto($message, $statusCode);

        $this->assertSame($message, $dto->message);
        $this->assertSame($statusCode, $dto->statusCode);
        $this->assertSame([], $dto->errors);
    }

    public function test_construct_with_empty_errors_array(): void
    {
        $message = 'Test error message';
        $statusCode = 404;
        $errors = [];

        $dto = new ErrorDto($message, $statusCode, $errors);

        $this->assertSame($message, $dto->message);
        $this->assertSame($statusCode, $dto->statusCode);
        $this->assertSame([], $dto->errors);
    }

    public function test_extends_spatie_data_class(): void
    {
        $message = 'Test error message';
        $statusCode = 400;

        $dto = new ErrorDto($message, $statusCode);

        $this->assertInstanceOf(Data::class, $dto);
    }

    public function test_to_response_returns_json_response_with_correct_status_code(): void
    {
        $message = 'Test error message';
        $statusCode = 422;
        $errors = ['field1' => 'Error 1', 'field2' => 'Error 2'];

        $dto = new ErrorDto($message, $statusCode, $errors);
        $request = Request::create('/test', 'GET');

        $response = $dto->toResponse($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame($statusCode, $response->getStatusCode());
    }

    public function test_to_response_contains_correct_data(): void
    {
        $message = 'Test error message';
        $statusCode = 400;
        $errors = ['field1' => 'Error 1'];

        $dto = new ErrorDto($message, $statusCode, $errors);
        $request = Request::create('/test', 'GET');

        $response = $dto->toResponse($request);
        $responseData = $response->getData(true);

        $this->assertSame($message, $responseData['message']);
        $this->assertSame($statusCode, $responseData['statusCode']);
        $this->assertSame($errors, $responseData['errors']);
    }

    public function test_default_wrap_returns_null(): void
    {
        $message = 'Test error message';
        $statusCode = 400;

        $dto = new ErrorDto($message, $statusCode);

        $this->assertNull($dto->defaultWrap());
    }

    public function test_can_handle_different_status_code_values(): void
    {
        $message = 'Test error message';
        $testStatusCodes = [200, 301, 400, 401, 403, 404, 422, 500, 503];

        foreach ($testStatusCodes as $statusCode) {
            $dto = new ErrorDto($message, $statusCode);
            $this->assertSame($statusCode, $dto->statusCode);
        }
    }

    public function test_can_handle_empty_message(): void
    {
        $message = '';
        $statusCode = 400;

        $dto = new ErrorDto($message, $statusCode);

        $this->assertSame('', $dto->message);
    }

    public function test_can_handle_long_message(): void
    {
        $message = str_repeat('This is a very long error message. ', 100);
        $statusCode = 500;

        $dto = new ErrorDto($message, $statusCode);

        $this->assertSame($message, $dto->message);
    }

    public function test_can_handle_complex_errors_array(): void
    {
        $message = 'Validation failed';
        $statusCode = 422;
        $errors = [
            'email' => 'The email field is required.',
            'password' => 'The password must be at least 8 characters.',
            'nested' => [
                'field1' => 'Nested error 1',
                'field2' => 'Nested error 2',
            ],
        ];

        $dto = new ErrorDto($message, $statusCode, $errors);

        $this->assertSame($errors, $dto->errors);
    }

    public function test_to_response_with_different_request_types(): void
    {
        $message = 'Test error message';
        $statusCode = 400;

        $dto = new ErrorDto($message, $statusCode);

        // Test with GET request
        $getRequest = Request::create('/test', 'GET');
        $getResponse = $dto->toResponse($getRequest);
        $this->assertSame($statusCode, $getResponse->getStatusCode());

        // Test with POST request
        $postRequest = Request::create('/test', 'POST');
        $postResponse = $dto->toResponse($postRequest);
        $this->assertSame($statusCode, $postResponse->getStatusCode());
    }
}
