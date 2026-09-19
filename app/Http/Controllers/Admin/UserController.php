<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Contracts\AuditLoggerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangeUserPasswordRequest;
use App\Http\Requests\Admin\ToggleUserStatusRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Identity\SecurityEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Vendor\LaravelAuthentication\Services\Password\PasswordService;

class UserController extends Controller
{
    public function __construct(private AuditLoggerInterface $auditLogger) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $users = User::query()
            ->with('roles')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.users.index', compact('users', 'search'));
    }

    public function show(User $user): View
    {
        $user->load('roles.permissions');
        $activities = $this->activityQuery($user)->limit(5)->get();

        return view('pages.admin.users.show', [
            'user' => $user,
            'roles' => Role::query()->where('guard_name', 'web')->orderBy('name')->get(),
            'activities' => $activities,
        ]);
    }

    public function activity(User $user): View
    {
        $activities = $this->activityQuery($user)
            ->paginate(20, ['*'], 'activity_page')
            ->withQueryString();

        return view('pages.admin.users.activity', compact('user', 'activities'));
    }

    private function activityQuery(User $user): Builder
    {
        return SecurityEvent::query()
            ->where(fn ($query) => $query
                ->whereIn('subject', [(string) $user->uuid, (string) $user->getKey()])
                ->orWhereIn('actor', [(string) $user->uuid, (string) $user->getKey()]))
            ->latest('occurred_at')
            ->latest('id');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $role = $request->string('role')->toString();
        $removesLastAdmin = $user->hasRole('platform_admin') && $role !== 'platform_admin';

        DB::transaction(function () use ($request, $user, $role, $removesLastAdmin): void {
            if ($removesLastAdmin && $this->activePlatformAdminCount() <= 1) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'At least one platform administrator must remain.');
            }

            $user->update($request->safe()->only(['name', 'email']));
            $user->syncRoles([$role]);

            $this->auditLogger->record(
                event: 'USER_ROLE_UPDATED',
                subject: (string) $user->uuid,
                actor: (string) $request->user()->uuid,
                risk: $role === 'platform_admin' ? 'high' : 'medium',
                metadata: ['role' => $role],
            );
        });

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated.');
    }

    public function updatePassword(ChangeUserPasswordRequest $request, User $user, PasswordService $passwordService): RedirectResponse
    {
        $passwordService->updatePassword($user, $request->string('password')->toString());
        $this->auditLogger->record(
            event: 'USER_PASSWORD_CHANGED',
            subject: (string) $user->uuid,
            actor: (string) $request->user()->uuid,
            risk: 'high',
            metadata: ['method' => 'admin_change'],
        );

        return redirect()->route('admin.users.show', $user)->with('success', 'User password changed.');
    }

    public function sendResetLink(Request $request, User $user): RedirectResponse
    {
        $request->validate([]);
        Password::broker()->sendResetLink(['email' => $user->email]);
        $this->auditLogger->record(
            event: 'USER_PASSWORD_RESET_LINK_SENT',
            subject: (string) $user->uuid,
            actor: (string) $request->user()->uuid,
            risk: 'medium',
            metadata: ['channel' => 'email'],
        );

        return redirect()->route('admin.users.show', $user)->with('status', 'If the account is eligible, a reset link was sent.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password:web'],
        ]);

        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $uuid = (string) $user->uuid;
        $deleted = DB::transaction(function () use ($request, $user, $uuid): bool {
            if ($user->hasRole('platform_admin') && $this->activePlatformAdminCount() <= 1) {
                return false;
            }

            $this->auditLogger->record(
                event: 'USER_DELETED',
                subject: $uuid,
                actor: (string) $request->user()->uuid,
                risk: 'critical',
                metadata: ['email' => $user->email],
            );

            $user->delete();

            return true;
        });

        if (! $deleted) {
            return back()->withErrors(['user' => 'At least one active platform administrator must remain.']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function toggleStatus(ToggleUserStatusRequest $request, User $user): RedirectResponse
    {
        $active = $request->boolean('active');
        if (! $active && $user->is($request->user())) {
            return back()->withErrors(['active' => 'You cannot deactivate your own account.']);
        }

        $updated = DB::transaction(function () use ($request, $user, $active): bool {
            if (! $active && $user->hasRole('platform_admin') && $this->activePlatformAdminCount() <= 1) {
                return false;
            }

            $user->update([
                'active' => $active,
                'status' => $active ? 'active' : 'inactive',
            ]);
            $this->auditLogger->record(
                event: $active ? 'USER_ACTIVATED' : 'USER_DEACTIVATED',
                subject: (string) $user->uuid,
                actor: (string) $request->user()->uuid,
                risk: 'high',
                metadata: ['active' => $active],
            );

            return true;
        });

        if (! $updated) {
            return back()->withErrors(['active' => 'At least one active platform administrator must remain.']);
        }

        return redirect()->route('admin.users.show', $user)->with('success', $active ? 'User activated.' : 'User deactivated.');
    }

    private function activePlatformAdminCount(): int
    {
        return User::query()
            ->where('active', true)
            ->where('status', 'active')
            ->whereHas('roles', fn ($query) => $query->where('name', 'platform_admin'))
            ->lockForUpdate()
            ->count();
    }
}
