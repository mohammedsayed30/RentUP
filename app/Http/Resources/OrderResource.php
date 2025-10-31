<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            
            'id' => $this->id,
            'code' => $this->code,
            
            'amount' => (float) $this->amount_decimal, 
            
            'currentStatus' => $this->status,
            'placedAt' => $this->placed_at->format('Y-m-d H:i:s'), 
            
            'user_id' => $this->user_id
        ];
    }
}
