<?php

namespace App\Http\Filters\OrdersIndexPipeline;

use Closure;
use App\Http\Filters\QueryPipe;

class OrderSortByDateOrAmount implements QueryPipe
{

    public function handle($query, Closure $next): mixed
    {
        if (request()->has('sort')) {
            $sortByInput = request('sort'); 
            
            // Default to ascending order
            $sortOrder = 'asc';
            
            // check if the input indicates descending order
            if (str_starts_with($sortByInput, '-')) {
                $sortOrder = 'desc';
                $column = substr($sortByInput, 1);  
            } else {
                
                $column = $sortByInput; 
            }

            if (in_array($column, ['placed_at', 'amount_decimal'])) {
                $query->orderBy($column, $sortOrder);
            }
        }

        return $next($query);
    }
    
}