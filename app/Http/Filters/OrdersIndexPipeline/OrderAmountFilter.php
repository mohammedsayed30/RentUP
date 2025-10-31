<?php

namespace App\Http\Filters\OrdersIndexPipeline;

use Closure;
use App\Http\Filters\QueryPipe;

// Filter to handle min and max order amount
class OrderAmountFilter implements QueryPipe
{

    public function handle($query, Closure $next): mixed
    {
        if (request()->has('min_amount')) {
            $query->where('amount_decimal', '>=', request('min_amount'));
        }

        if (request()->has('max_amount')) {
            $query->where('amount_decimal', '<=', request('max_amount'));
        }

        return $next($query);
    }
    
}