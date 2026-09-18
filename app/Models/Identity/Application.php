<?php

namespace App\Models\Identity;

use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'protocol_mode',
        'client_type',
        'status',
        'description',
        'homepage_url',
        'privacy_url',
        'terms_url',
        'consent_policy',
        'session_policy_json',
        'claim_policy_version',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'session_policy_json' => 'array',
            'claim_policy_version' => 'integer',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function redirectUris(): HasMany
    {
        return $this->hasMany(ApplicationRedirectUri::class);
    }

    public function scopes(): BelongsToMany
    {
        return $this->belongsToMany(Scope::class, 'application_scopes')
            ->withPivot(['allowed', 'consent_required'])
            ->withTimestamps();
    }
}
