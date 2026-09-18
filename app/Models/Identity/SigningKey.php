<?php

namespace App\Models\Identity;

use Database\Factories\Identity\SigningKeyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SigningKey extends Model
{
    /** @use HasFactory<SigningKeyFactory> */
    use HasFactory;

    protected $fillable = [
        'kid',
        'algorithm',
        'public_key',
        'status',
        'active_slot',
        'activated_at',
        'retired_at',
    ];

    protected $hidden = [
        'private_key',
    ];

    protected function casts(): array
    {
        return [
            'private_key' => 'encrypted',
            'activated_at' => 'datetime',
            'retired_at' => 'datetime',
        ];
    }
}
