<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases;

use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 * @template TSearch of SearchDto
 */
abstract class Repository {}
