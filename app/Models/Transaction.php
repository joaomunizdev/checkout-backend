<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'card_id',
        'plan_id',
        'coupon_id',
        'subscription_id',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'card_id' => 'integer',
            'plan_id' => 'integer',
            'coupon_id' => 'integer',
            'subscription_id' => 'integer',
            'email' => 'string',
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
