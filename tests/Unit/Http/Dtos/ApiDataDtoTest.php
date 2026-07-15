<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Http\Dtos;

use Curicows\LaravelCommon\Http\Dtos\ApiDataDto;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use Spatie\LaravelData\Data;

#[CoversClass(ApiDataDto::class)]
class ApiDataDtoTest extends TestCase
{
    public function test_construct_sets_data_property_with_array(): void
    {
        $data = ['item1', 'item2', 'item3'];

        $dto = new ApiDataDto($data);

        $this->assertSame($data, $dto->data);
    }

    public function test_construct_sets_data_property_with_collection(): void
    {
        $data = new Collection(['item1', 'item2', 'item3']);

        $dto = new ApiDataDto($data);

        $this->assertSame($data, $dto->data);
    }

    public function test_construct_sets_data_property_with_empty_array(): void
    {
        $data = [];

        $dto = new ApiDataDto($data);

        $this->assertSame($data, $dto->data);
    }

    public function test_construct_sets_data_property_with_empty_collection(): void
    {
        $data = new Collection([]);

        $dto = new ApiDataDto($data);

        $this->assertSame($data, $dto->data);
    }

    public function test_construct_sets_data_property_with_associative_array(): void
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];

        $dto = new ApiDataDto($data);

        $this->assertSame($data, $dto->data);
    }

    public function test_construct_sets_data_property_with_nested_array(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ];

        $dto = new ApiDataDto($data);

        $this->assertSame($data, $dto->data);
    }

    public function test_data_property_is_readonly(): void
    {
        $data = ['item1', 'item2'];

        $dto = new ApiDataDto($data);

        $reflection = new ReflectionClass($dto);
        $dataProperty = $reflection->getProperty('data');

        $this->assertTrue($dataProperty->isReadOnly(), 'Property data should be readonly');
    }

    public function test_extends_spatie_data_class(): void
    {
        $data = ['item1', 'item2'];

        $dto = new ApiDataDto($data);

        $this->assertInstanceOf(Data::class, $dto);
    }

    public function test_can_handle_mixed_data_types_in_array(): void
    {
        $data = [
            'string',
            123,
            true,
            null,
            ['nested' => 'array'],
        ];

        $dto = new ApiDataDto($data);

        $this->assertSame($data, $dto->data);
    }

    public function test_can_handle_large_arrays(): void
    {
        $data = range(1, 1000);

        $dto = new ApiDataDto($data);

        $this->assertSame($data, $dto->data);
        $this->assertCount(1000, $dto->data);
    }

    public function test_data_property_accepts_both_array_and_collection_types(): void
    {
        // Test with array
        $arrayData = ['item1', 'item2'];
        $arrayDto = new ApiDataDto($arrayData);
        $this->assertIsArray($arrayDto->data);

        // Test with Collection
        $collectionData = new Collection(['item1', 'item2']);
        $collectionDto = new ApiDataDto($collectionData);
        $this->assertInstanceOf(Collection::class, $collectionDto->data);
    }
}
