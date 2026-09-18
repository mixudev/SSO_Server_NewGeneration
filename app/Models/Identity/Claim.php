<?php

namespace App\Models\Identity;

use App\Domain\Identity\Enums\ClaimValueType;
use Database\Factories\Identity\ClaimFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    /** @use HasFactory<ClaimFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'description',
        'source',
        'value_type',
        'sensitivity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'value_type' => ClaimValueType::class,
        ];
    }
}
