<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    protected $fillable = [
        'provider',
        'provider_character_id',
        'name',
        'work',
        'image_url',
        'popularity',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'popularity' => 'integer',
            'raw_payload' => 'array',
        ];
    }
}
