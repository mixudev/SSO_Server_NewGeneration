<?php

namespace App\Models;

use Database\Factories\ScopeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Scope extends Model
{
    /** @use HasFactory<ScopeFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'category',
        'risk_level',
        'is_system',
        'is_default',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class, 'application_scopes')
            ->withPivot(['allowed', 'consent_required'])
            ->withTimestamps();
    }
}
