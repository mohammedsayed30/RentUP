<?php

namespace App\Http\Filters\OrdersIndexPipeline;

use Closure;
use App\Http\Filters\QueryPipe;

class OrderDateFilter implements QueryPipe
{

    public function handle($query, Closure $next): mixed
    {
        if (request()->has('date_from')) {
            $query->where('placed_at', '>=', request('date_from'));
        }

        if (request()->has('date_to')) {
            $query->where('placed_at', '<=', request('date_to'));
        }

        return $next($query);
    }
    
}