<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Card extends Model
{
    protected $fillable = [
        'card_number',
        'client_name',
        'expire_date',
        'cvc',
        'card_flag_id',
    ];

    protected function casts(): array
    {
        return [
            'card_number' => 'integer',
            'client_name' => 'string',
            'expire_date' => 'date',
            'cvc' => 'integer',
            'card_flag_id' => 'integer',
        ];
    }

    public function cardFlag(): BelongsTo
    {
        return $this->belongsTo(CardFlag::class);
    }
}
