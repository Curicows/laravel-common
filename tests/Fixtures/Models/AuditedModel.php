<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Fixtures\Models;

use Curicows\LaravelCommon\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditedModel extends Model
{
    use CreatedUpdatedBy, HasUuids, SoftDeletes;

    protected $table = 'audited_models';

    protected $guarded = [];
}
