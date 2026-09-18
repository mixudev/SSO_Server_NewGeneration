<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Contracts\AuditLoggerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrganizationRequest;
use App\Models\Identity\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function __construct(private AuditLoggerInterface $auditLogger) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $organizations = Organization::query()
            ->withCount('applications')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.organizations.index', compact('organizations', 'search', 'status'));
    }

    public function show(Organization $organization): View
    {
        $organization->loadCount('applications');

        return view('pages.admin.organizations.show', compact('organization'));
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): RedirectResponse
    {
        $before = $organization->only(['name', 'slug', 'status']);
        $organization->update($request->validated());
        $this->auditLogger->record(
            event: 'ORGANIZATION_UPDATED',
            subject: (string) $organization->getKey(),
            actor: (string) $request->user()->getAuthIdentifier(),
            risk: $request->validated('status') !== $before['status'] ? 'high' : 'medium',
            metadata: ['before' => $before, 'after' => $organization->only(['name', 'slug', 'status'])],
        );

        return redirect()->route('admin.organizations.show', $organization)->with('success', 'Organization updated.');
    }
}
