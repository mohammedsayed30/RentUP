<?php

namespace App\Http\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

interface QueryPipe
{
    public function handle(Builder $query, Closure $next): mixed;
}