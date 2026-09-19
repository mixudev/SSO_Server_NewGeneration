<?php

namespace App\Models\Identity;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationUserAccess extends Model
{
    use HasUlids;

    protected $table = 'application_user_access';

    protected $fillable = [
        'application_id',
        'user_id',
        'status',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
