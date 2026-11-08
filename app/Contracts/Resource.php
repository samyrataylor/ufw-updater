<?php

namespace App\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Stringable;

interface Resource extends Stringable, Jsonable, Arrayable
{
    public const bool RECURSE = true;

    public static function from(mixed $from = null): ?static;

    public function toString(): string;
}
