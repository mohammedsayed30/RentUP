<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\NotificationLog;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends Model
{
    //has factory
    use HasFactory;
    //
    protected $fillable = ['user_id', 'token', 'platform', 'last_seen_at', 'is_valid'];
    protected $casts = [
        'last_seen_at' => 'datetime',
        'is_valid' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class);
    }
}
