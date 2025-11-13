<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'plan_id',
        'coupon_id',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'plan_id' => 'integer',
            'coupon_id' => 'integer',
            'email' => 'string',
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
}
