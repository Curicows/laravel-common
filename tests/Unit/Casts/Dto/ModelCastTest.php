<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Casts\Dto;

use Curicows\LaravelCommon\Casts\Dto\ModelCast;
use Curicows\LaravelCommon\Tests\TestCase;
use Curicows\LaravelCommon\Tests\Unit\Casts\Dto\Fixtures\TestUserIdEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Exceptions\CannotCastData;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\ValidationStrategy;
use Spatie\LaravelData\Support\DataAttributesCollection;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\DataPropertyType;
use Spatie\LaravelData\Support\Types\NamedType;

#[CoversClass(ModelCast::class)]
class ModelCastTest extends TestCase
{
    public function test_returns_existing_model_instance(): void
    {
        $cast = new ModelCast;
        $user = new TestModel;
        $property = $this->createDataProperty(TestModel::class);

        $result = $cast->cast($property, $user, [], $this->creationContext());

        $this->assertSame($user, $result);
    }

    public function test_resolves_model_by_id(): void
    {
        $user = new TestModel;
        $cast = new StubModelCast($user);
        $property = $this->createDataProperty(TestModel::class);

        $result = $cast->cast($property, 'user-id', [], $this->creationContext());

        $this->assertSame($user, $result);
        $this->assertSame(TestModel::class, $cast->lastClassName);
        $this->assertSame('user-id', $cast->lastValue);
    }

    public function test_nullable_missing_model_returns_null(): void
    {
        $cast = new StubModelCast(null);
        $property = $this->createDataProperty(TestModel::class, nullable: true);

        $result = $cast->cast($property, 'missing-id', [], $this->creationContext());

        $this->assertNull($result);
    }

    public function test_non_nullable_missing_model_throws_cannot_cast_data(): void
    {
        $cast = new StubModelCast(null);
        $property = $this->createDataProperty(TestModel::class);

        $this->expectException(CannotCastData::class);
        $this->expectExceptionMessage('The target model was not found.');

        $cast->cast($property, 'missing-id', [], $this->creationContext());
    }

    public function test_resolves_model_from_backed_enum_value(): void
    {
        $user = new TestModel;
        $cast = new StubModelCast($user);
        $property = $this->createDataProperty(TestModel::class);

        $result = $cast->cast($property, TestUserIdEnum::Primary, [], $this->creationContext());

        $this->assertSame($user, $result);
        $this->assertSame(TestUserIdEnum::Primary->value, $cast->lastValue);
    }

    public function test_find_model_applies_with_trashed_when_enabled(): void
    {
        $query = \Mockery::mock(Builder::class);
        $query->shouldReceive('withTrashed')->once()->andReturnSelf();
        $query->shouldReceive('find')->once()->with('user-id')->andReturn(null);

        $modelClass = new class extends Model
        {
            public static ?Builder $testQuery = null;

            public static function query(): Builder
            {
                return self::$testQuery;
            }
        };

        $modelClass::$testQuery = $query;

        $cast = new TestableModelCast(withTrashed: true);

        $this->assertNull($cast->findModelForTest($modelClass::class, 'user-id'));
    }

    public function test_find_model_does_not_apply_with_trashed_by_default(): void
    {
        $query = \Mockery::mock(Builder::class);
        $query->shouldReceive('find')->once()->with('user-id')->andReturn(null);
        $query->shouldNotReceive('withTrashed');

        $modelClass = new class extends Model
        {
            public static ?Builder $testQuery = null;

            public static function query(): Builder
            {
                return self::$testQuery;
            }
        };

        $modelClass::$testQuery = $query;

        $cast = new TestableModelCast;

        $this->assertNull($cast->findModelForTest($modelClass::class, 'user-id'));
    }

    private function createDataProperty(string $modelClass, bool $nullable = false): DataProperty
    {
        $namedType = new NamedType(
            name: $modelClass,
            builtIn: false,
            acceptedTypes: [$modelClass],
            kind: DataTypeKind::Default,
            dataClass: null,
            dataCollectableClass: null,
        );

        $propertyType = new DataPropertyType(
            type: $namedType,
            isOptional: false,
            isNullable: $nullable,
            isMixed: false,
            lazyType: null,
            kind: DataTypeKind::Default,
            dataClass: null,
            dataCollectableClass: null,
            iterableClass: null,
            iterableItemType: null,
            iterableKeyType: null,
        );

        return new DataProperty(
            name: 'user',
            className: 'TestData',
            type: $propertyType,
            validate: true,
            computed: false,
            hidden: false,
            isPromoted: true,
            isReadonly: false,
            isVirtual: false,
            morphable: false,
            autoLazy: null,
            hasDefaultValue: false,
            defaultValue: null,
            cast: null,
            transformer: null,
            inputMappedName: null,
            outputMappedName: null,
            attributes: new DataAttributesCollection,
        );
    }

    private function creationContext(): CreationContext
    {
        return new CreationContext(
            dataClass: 'TestData',
            mappedProperties: [],
            currentPath: [],
            validationStrategy: ValidationStrategy::Disabled,
            mapPropertyNames: false,
            disableMagicalCreation: true,
            useOptionalValues: false,
            ignoredMagicalMethods: null,
            casts: null,
        );
    }
}

class TestModel extends Model {}

class StubModelCast extends ModelCast
{
    public ?string $lastClassName = null;

    public mixed $lastValue = null;

    public function __construct(private readonly ?Model $resolvedModel)
    {
        parent::__construct();
    }

    protected function findModel(string $className, mixed $value): ?Model
    {
        $this->lastClassName = $className;
        $this->lastValue = $value;

        return $this->resolvedModel;
    }
}

class TestableModelCast extends ModelCast
{
    public function __construct(bool $withTrashed = false)
    {
        parent::__construct($withTrashed);
    }

    public function findModelForTest(string $className, mixed $value): ?Model
    {
        return $this->findModel($className, $value);
    }
}
