<?php

namespace App\Http\Filters\OrdersIndexPipeline;

use Closure;
use App\Http\Filters\QueryPipe;


class OrderStatusFilter implements QueryPipe
{

    public function handle($query, Closure $next): mixed
    {
        if (request()->has('status')) {
            //handle multiple statuses separated by commas
            $statuses = explode(',', request('status'));
            $statuses = array_filter(array_map('trim', $statuses));
            
            if (!empty($statuses)) {
                $query->whereIn('status', $statuses);
            }
        }

        return $next($query);
    }
    
}
