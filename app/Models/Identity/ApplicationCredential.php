<?php

namespace App\Models\Identity;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationCredential extends Model
{
    use HasUlids;

    protected $fillable = [
        'application_id',
        'passport_client_id',
        'status',
        'generation',
        'revoked_at',
    ];

    protected $hidden = [
        'passport_client_id',
    ];

    protected function casts(): array
    {
        return [
            'generation' => 'integer',
            'revoked_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
