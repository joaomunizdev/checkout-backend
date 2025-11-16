<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OA;

/**
 * @OA\Schema(
 * schema="Plan",
 * title="Plan",
 * description="Plan Model",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="name", type="string", description="Name of the plan", example="PRO_MONTHLY"),
 * @OA\Property(property="description", type="string", description="Description of the plan", example="Pro Anual"),
 * @OA\Property(property="price", type="number", format="float", example=99.90),
 * @OA\Property(property="periodicity", type="integer", description="Plan duration in days", example=30),
 * @OA\Property(property="active", type="boolean", example=true)
 * )
 */
class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'periodicity',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'description' => 'string',
            'price' => 'float',
            'periodicity' => 'integer',
            'active' => 'boolean',
        ];
    }

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
