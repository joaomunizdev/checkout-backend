<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
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
