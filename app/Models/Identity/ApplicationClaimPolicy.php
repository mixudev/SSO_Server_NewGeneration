<?php

namespace App\Models\Identity;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationClaimPolicy extends Model
{
    use HasUlids;

    protected $fillable = [
        'application_id',
        'version',
        'rules_json',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'rules_json' => 'array',
            'version' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
