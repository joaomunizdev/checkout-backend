<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OA;

/**
 * @OA\Schema(
 * schema="Card",
 * title="Card (Complete Model)",
 * description="Card Model (includes sensitive data)",
 * @OA\Property(property="id", type="integer"),
 * @OA\Property(property="card_number", type="string"),
 * @OA\Property(property="client_name", type="string"),
 * @OA\Property(property="expire_date", type="string", format="date"),
 * @OA\Property(property="cvc", type="string"),
 * @OA\Property(property="card_flag_id", type="integer")
 * )
 */
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
