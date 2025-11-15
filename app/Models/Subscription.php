<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OA;

/**
 * @OA\Schema(
 * schema="Subscription",
 * title="Subscription",
 * description="Subscription Data",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="plan_id", type="integer", example=1),
 * @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 * @OA\Property(property="coupon_id", type="integer", nullable=true, example=1),
 * @OA\Property(property="price_paid", type="number", format="float", example=99.90),
 * @OA\Property(property="active", type="boolean", example=true),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'coupon_id',
        'email',
        'active',
        'price_paid',
    ];

    protected function casts(): array
    {
        return [
            'plan_id' => 'integer',
            'coupon_id' => 'integer',
            'email' => 'string',
            'active' => 'boolean',
            'price_paid' => 'float'
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }
}
