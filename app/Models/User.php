<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'avatar_path', 'active', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            $user->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function isAccountActive(): bool
    {
        return $this->active !== false && ($this->status ?? 'active') !== 'inactive';
    }

    public function getAuthPassword(): string
    {
        if ($this->isAccountActive()) {
            return (string) $this->getAttribute('password');
        }

        static $inactivePasswordHash;
        $inactivePasswordHash ??= Hash::make(Str::random(64));

        return $inactivePasswordHash;
    }

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function getAuthIdentifier(): string
    {
        return (string) $this->getKey();
    }
}
