<?php

namespace App\Models\OAuth;

use App\Domain\OAuth\Enums\AuthorizationTransactionStatus;
use App\Models\Identity\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthorizationTransaction extends Model
{
    use HasUlids;

    protected $fillable = [
        'transaction_id_hash',
        'authorization_code_hash',
        'client_id',
        'application_id',
        'user_id',
        'redirect_uri_hash',
        'response_type',
        'scope_string',
        'state_hash',
        'nonce_hash',
        'nonce_encrypted',
        'code_challenge',
        'code_challenge_method',
        'status',
        'expires_at',
        'authenticated_at',
        'consented_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AuthorizationTransactionStatus::class,
            'expires_at' => 'datetime',
            'authenticated_at' => 'datetime',
            'consented_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
