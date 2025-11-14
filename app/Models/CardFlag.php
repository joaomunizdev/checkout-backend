<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OA;

/**
 * @OA\Schema(
 * schema="CardFlag",
 * title="CardFlag",
 * description="Card Brand",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="name", type="string", example="Visa")
 * )
 */
class CardFlag extends Model
{
    protected $fillable = [
        'name',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'string',
        ];
    }
}
