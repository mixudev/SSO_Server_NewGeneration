<?php

namespace App\Models\Identity;

use Database\Factories\Identity\ApplicationRedirectUriFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationRedirectUri extends Model
{
    /** @use HasFactory<ApplicationRedirectUriFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'application_id',
        'uri',
        'uri_hash',
        'kind',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
