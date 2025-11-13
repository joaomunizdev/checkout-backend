<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
