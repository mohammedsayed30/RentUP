<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 * schema="User",
 * title="User Model",
 * description="User data model",
 * @OA\Property(property="id", type="integer", readOnly=true, example=5),
 * @OA\Property(property="name", type="string", example="Jane Doe"),
 * @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
 * @OA\Property(property="created_at", type="string", format="date-time", readOnly=true)
 * )
 */



class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    //relation with orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    //relation with device tokens
    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }
}
