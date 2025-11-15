<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OA;

/**
 * @OA\Schema(
 * schema="Coupon",
 * title="Coupon",
 * description="Discount Coupon Model",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="name", type="string", description="The coupon code", example="SAVE30"),
 * @OA\Property(property="plan_id", type="integer", nullable=true, description="Specific plan ID (null for global)", example=1),
 * @OA\Property(property="expiration_days", type="integer", nullable=true, description="Days to expire (null for never)", example=5),
 * @OA\Property(property="amount_of_uses", type="integer", description="Total number of uses allowed", example=2),
 * @OA\Property(property="discount_percent", type="number", format="float", nullable=true, example=20.0),
 * @OA\Property(property="discount_amount", type="number", format="float", nullable=true, example=30.00)
 * )
 */
class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'plan_id',
        'expiration_days',
        'amount_of_uses',
        'discount_percent',
        'discount_amount',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'plan_id' => 'integer',
            'expiration_days' => 'integer',
            'amount_of_uses' => 'integer',
            'discount_percent' => 'float',
            'discount_amount' => 'float',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
