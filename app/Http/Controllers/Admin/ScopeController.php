<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Contracts\AuditLoggerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreScopeRequest;
use App\Models\Identity\Scope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScopeController extends Controller
{
    public function __construct(private AuditLoggerInterface $auditLogger) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $scopes = Scope::query()->withCount(['applications as active_applications_count' => fn ($query) => $query->where('status', 'active')])->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))->orderBy('name')->paginate(20)->withQueryString();

        return view('pages.admin.scopes.index', compact('scopes', 'search'));
    }

    public function store(StoreScopeRequest $request): RedirectResponse
    {
        $scope = Scope::query()->create($request->validated());
        $this->auditLogger->record(
            event: 'SCOPE_CREATED',
            subject: (string) $scope->getKey(),
            actor: (string) $request->user()->getAuthIdentifier(),
            risk: $scope->risk_level === 'critical' ? 'high' : 'medium',
            metadata: ['name' => $scope->name, 'risk_level' => $scope->risk_level],
        );

        return redirect()->route('admin.scopes.index')->with('success', 'Scope created.');
    }
}
