<?php

namespace App\Http\Filters\OrdersIndexPipeline;
use Closure;
use App\Http\Filters\QueryPipe;

// Filter to handle order code filtering

class OrderCodeSearchFilter implements QueryPipe
{

    public function handle($query, Closure $next): mixed
    {
        //search in order code
        if (request()->has('q')) {
            $query->where('code', 'like', '%' . request('q') . '%');
        }

        return $next($query);
    }
    
}