<?php

namespace App\Models\Identity;

use Illuminate\Database\Eloquent\Model;

class SecurityEvent extends Model
{
    protected $fillable = [
        'event',
        'request_id',
        'organization_id',
        'application_id',
        'subject',
        'actor',
        'risk',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    /** @return array<string, mixed> */
    public function safeMetadata(): array
    {
        return $this->redactMetadata($this->metadata ?? []);
    }

    /** @param array<string, mixed> $metadata */
    private function redactMetadata(array $metadata): array
    {
        $sensitiveFragments = ['password', 'secret', 'token', 'private_key', 'authorization', 'credential', 'recovery'];
        $safe = [];

        foreach ($metadata as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            $isSensitive = collect($sensitiveFragments)->contains(
                fn (string $fragment): bool => str_contains($normalizedKey, $fragment),
            );
            $safe[$key] = $isSensitive
                ? '[REDACTED]'
                : (is_array($value) ? $this->redactMetadata($value) : $value);
        }

        return $safe;
    }
}
