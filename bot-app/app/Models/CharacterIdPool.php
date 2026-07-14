<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterIdPool extends Model
{
    protected $table = 'character_id_pool';

    protected $fillable = [
        'provider',
        'provider_character_id',
        'name',
        'popularity_score',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'popularity_score' => 'integer',
        ];
    }
}
