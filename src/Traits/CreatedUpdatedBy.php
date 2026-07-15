<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Traits;

use Curicows\LaravelCommon\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin Model
 * @mixin SoftDeletes
 */
trait CreatedUpdatedBy
{
    public static function bootCreatedUpdatedBy(): void
    {
        static::creating(function (Model $model): void {
            $user = auth()->user();

            if ($user) {
                if (! $model->isDirty('created_by')) {
                    $model->setAttribute('created_by', $user->getAuthIdentifier());
                }

                if (! $model->isDirty('updated_by')) {
                    $model->setAttribute('updated_by', $user->getAuthIdentifier());
                }
            }
        });

        static::updating(function (Model $model): void {
            $user = auth()->user();

            if (! $model->isDirty('updated_by') && $user) {
                $model->setAttribute('updated_by', $user->getAuthIdentifier());
            }
        });

        if (method_exists(static::class, 'softDeleted')) {
            static::softDeleted(function (Model $model): void {
                $user = auth()->user();

                if (! $model->isDirty('deleted_by') && $user) {
                    $model->setAttribute('deleted_by', $user->getAuthIdentifier());
                }
            });
        }
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsToCreatedUpdatedByUser('created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsToCreatedUpdatedByUser('updated_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsToCreatedUpdatedByUser('deleted_by');
    }

    protected function belongsToCreatedUpdatedByUser(string $foreignKey): BelongsTo
    {
        $userModel = $this->createdUpdatedByUserModel();
        $relation = $this->belongsTo($userModel, $foreignKey);

        if (in_array(SoftDeletes::class, class_uses_recursive($userModel), true)) {
            $relation->withTrashed();
        }

        return $relation;
    }

    protected function createdUpdatedByUserModel(): string
    {
        return User::configuredModelClass();
    }
}
