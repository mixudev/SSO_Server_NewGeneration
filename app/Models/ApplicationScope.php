<?php

namespace App\Models;

use Database\Factories\ApplicationScopeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationScope extends Model
{
    /** @use HasFactory<ApplicationScopeFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'application_id',
        'scope_id',
        'allowed',
        'consent_required',
    ];

    protected function casts(): array
    {
        return [
            'allowed' => 'boolean',
            'consent_required' => 'boolean',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }
}
