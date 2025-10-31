<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 * schema="Order",
 * title="Order Model",
 * description="Order data model",
 * @OA\Property(property="id", type="integer", readOnly=true, example=101),
 * @OA\Property(property="user_id", type="integer", readOnly=true, example=5),
 * @OA\Property(property="code", type="string", example="ORD-98765"),
 * @OA\Property(property="status", type="string", enum={"pending", "processing", "shipped", "delivered"}, example="pending"),
 * @OA\Property(property="amount_decimal", type="number", format="float", example=49.99),
 * @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 * @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true)
 * )
 */
class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * We exclude 'placed_at' since it uses a default value in the DB.
     */
    protected $fillable = [
        'user_id',
        'code',
        'amount_decimal',
        'placed_at',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'placed_at' => 'datetime',
        'amount_decimal' => 'decimal:2', 
    ];

    protected $attributes = [
     'status' => 'placed',
    ];
   
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * We often make created_at/updated_at/id hidden, but keeping them here for standard API use.
     */
    protected $hidden = [
        //
    ];
    

    // Automatically generate a unique order code when creating a new order if not set
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->code)) {
                $order->code = self::generateUniqueCode();
            }
        });
    }

    /**
     * Generates a unique, short order code.
     * Format example: O-20251030-ABCDE
     *
     * @return string
     */
    protected static function generateUniqueCode(): string
    {
        do {
            
            $datePart = now()->format('Ymd');
            
            $randomPart = Str::upper(Str::random(5));
            
            
            $code = "O-{$datePart}-{$randomPart}";
            
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class);
    }
}