<?php

namespace App\Models\Identity;

use Illuminate\Database\Eloquent\Model;

class SecurityEvent extends Model
{
    protected $fillable = [
        'event',
        'request_id',
        'organization_id',
        'application_id',
        'subject',
        'actor',
        'risk',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }
}
