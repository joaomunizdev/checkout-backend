<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OA;

/**
 * @OA\Schema(
 * schema="Transaction",
 * title="Transaction",
 * description="Transaction Model (Payment Record)",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="card_id", type="integer", example=1),
 * @OA\Property(property="plan_id", type="integer", example=1),
 * @OA\Property(property="coupon_id", type="integer", nullable=true, example=1),
 * @OA\Property(property="subscription_id", type="integer", example=1),
 * @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Transaction extends Model
{
    protected $fillable = [
        'card_id',
        'subscription_id',
    ];

    protected function casts(): array
    {
        return [
            'card_id' => 'integer',
            'subscription_id' => 'integer',
        ];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
