<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'periodicity',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'price' => 'float',
            'periodicity' => 'integer',
            'active' => 'boolean',
        ];
    }
}
