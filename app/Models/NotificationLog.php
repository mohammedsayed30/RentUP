<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Order;
use App\Models\DeviceToken;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class NotificationLog extends Model
{

    //has factory
    use HasFactory;
    protected $fillable = [
    'user_id', 'order_id', 'device_token_id', 'payload', 'response', 'status', 'sent_at'
    ];
    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function deviceToken()
    {
        return $this->belongsTo(DeviceToken::class);
    }
}
