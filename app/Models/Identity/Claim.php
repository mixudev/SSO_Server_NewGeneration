<?php

namespace App\Models\Identity;

use Database\Factories\ClaimFactory;
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
}
