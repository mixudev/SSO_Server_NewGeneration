<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Contracts\AuditLoggerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClaimRequest;
use App\Models\Identity\Claim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClaimController extends Controller
{
    public function __construct(private AuditLoggerInterface $auditLogger) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $claims = Claim::query()->when($search !== '', fn ($query) => $query->where('key', 'like', "%{$search}%"))->orderBy('key')->paginate(20)->withQueryString();

        return view('pages.admin.claims.index', compact('claims', 'search'));
    }

    public function store(StoreClaimRequest $request): RedirectResponse
    {
        $claim = Claim::query()->create($request->validated());
        $this->auditLogger->record(
            event: 'CLAIM_CREATED',
            subject: (string) $claim->getKey(),
            actor: (string) $request->user()->getAuthIdentifier(),
            risk: $claim->sensitivity === 'sensitive' ? 'high' : 'medium',
            metadata: ['key' => $claim->key, 'source' => $claim->source, 'sensitivity' => $claim->sensitivity],
        );

        return redirect()->route('admin.claims.index')->with('success', 'Claim created.');
    }
}
