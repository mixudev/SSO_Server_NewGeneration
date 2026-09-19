<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Contracts\AuditLoggerInterface;
use App\Http\Controllers\Controller;
use App\Models\Identity\Application;
use App\Models\Identity\ApplicationUserAccess;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

final class ApplicationAccessController extends Controller
{
    public function __construct(private AuditLoggerInterface $auditLogger) {}

    public function index(Application $application): View
    {
        return view('pages.admin.applications.access', [
            'application' => $application->load('organization'),
            'accesses' => $application->userAccess()->with('user')->latest()->paginate(20),
            'users' => User::query()->where('active', true)->where('status', 'active')->orderBy('name')->get(['id', 'uuid', 'name', 'email']),
        ]);
    }

    public function store(Request $request, Application $application): RedirectResponse
    {
        $values = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('active', true)->where('status', 'active')),
                Rule::unique('application_user_access', 'user_id')->where(fn ($query) => $query->where('application_id', $application->getKey())),
            ],
        ]);
        $access = $application->userAccess()->create(['user_id' => $values['user_id'], 'status' => 'active']);
        $this->auditLogger->record(
            event: 'APPLICATION_USER_ACCESS_GRANTED',
            subject: (string) $access->getKey(),
            actor: (string) $request->user()->uuid,
            risk: 'high',
            metadata: ['application_id' => (string) $application->getKey(), 'user_id' => (int) $values['user_id']],
        );

        return redirect()->route('admin.applications.access.index', $application)->with('success', 'Application access granted.');
    }

    public function destroy(Request $request, Application $application, ApplicationUserAccess $access): RedirectResponse
    {
        if ((string) $access->application_id !== (string) $application->getKey()) {
            abort(Response::HTTP_NOT_FOUND);
        }
        $access->update(['status' => 'revoked']);
        $this->auditLogger->record(
            event: 'APPLICATION_USER_ACCESS_REVOKED',
            subject: (string) $access->getKey(),
            actor: (string) $request->user()->uuid,
            risk: 'high',
            metadata: ['application_id' => (string) $application->getKey(), 'user_id' => (int) $access->user_id],
        );

        return redirect()->route('admin.applications.access.index', $application)->with('success', 'Application access revoked.');
    }
}
