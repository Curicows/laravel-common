<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Casts\Dto;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Exceptions\CannotCastData;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Types\NamedType;

class ModelCast implements Cast
{
    public function __construct(
        protected bool $withTrashed = false,
    ) {}

    /**
     * @throws CannotCastData
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): ?Model
    {
        if ($value instanceof Model) {
            return $value;
        }
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        /** @var NamedType $classType */
        $classType = $property->type->type;
        $model = $this->findModel($classType->name, $value);

        if (! $model) {
            return ! $property->type->isNullable ? throw new CannotCastData('The target model was not found.') : null;
        }

        return $model;
    }

    /**
     * @param  class-string<Model>  $className
     */
    protected function findModel(string $className, mixed $value): ?Model
    {
        $query = $className::query();

        if ($this->withTrashed) {
            $query->withTrashed();
        }

        return $query->find($value);
    }
}
