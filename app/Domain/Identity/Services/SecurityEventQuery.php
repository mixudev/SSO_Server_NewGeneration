<?php

namespace App\Domain\Identity\Services;

use App\Models\Identity\SecurityEvent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

final class SecurityEventQuery
{
    /** @return LengthAwarePaginator<int, SecurityEvent> */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $validated = $request->validate([
            'event' => ['nullable', 'string', 'max:100'],
            'risk' => ['nullable', 'in:low,medium,high,critical'],
            'actor' => ['nullable', 'string', 'max:191'],
            'subject' => ['nullable', 'string', 'max:191'],
            'organization_id' => ['nullable', 'string', 'max:64'],
            'application_id' => ['nullable', 'string', 'max:64'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:50'],
        ]);

        return SecurityEvent::query()
            ->when($validated['event'] ?? null, fn ($query, string $value) => $query->where('event', $value))
            ->when($validated['risk'] ?? null, fn ($query, string $value) => $query->where('risk', $value))
            ->when($validated['actor'] ?? null, fn ($query, string $value) => $query->where('actor', 'like', "%{$value}%"))
            ->when($validated['subject'] ?? null, fn ($query, string $value) => $query->where('subject', 'like', "%{$value}%"))
            ->when($validated['organization_id'] ?? null, fn ($query, string $value) => $query->where('organization_id', $value))
            ->when($validated['application_id'] ?? null, fn ($query, string $value) => $query->where('application_id', $value))
            ->latest('occurred_at')
            ->latest('id')
            ->paginate($validated['per_page'] ?? 20)
            ->withQueryString();
    }

    /** @return list<SecurityEvent> */
    public function export(Request $request): array
    {
        $validated = $request->validate([
            'event' => ['nullable', 'string', 'max:100'],
            'risk' => ['nullable', 'in:low,medium,high,critical'],
            'actor' => ['nullable', 'string', 'max:191'],
            'subject' => ['nullable', 'string', 'max:191'],
            'organization_id' => ['nullable', 'string', 'max:64'],
            'application_id' => ['nullable', 'string', 'max:64'],
        ]);

        return SecurityEvent::query()
            ->when($validated['event'] ?? null, fn ($query, string $value) => $query->where('event', $value))
            ->when($validated['risk'] ?? null, fn ($query, string $value) => $query->where('risk', $value))
            ->when($validated['actor'] ?? null, fn ($query, string $value) => $query->where('actor', 'like', "%{$value}%"))
            ->when($validated['subject'] ?? null, fn ($query, string $value) => $query->where('subject', 'like', "%{$value}%"))
            ->when($validated['organization_id'] ?? null, fn ($query, string $value) => $query->where('organization_id', $value))
            ->when($validated['application_id'] ?? null, fn ($query, string $value) => $query->where('application_id', $value))
            ->latest('occurred_at')
            ->latest('id')
            ->limit(1000)
            ->get()
            ->all();
    }
}
