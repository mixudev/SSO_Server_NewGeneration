<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Contracts\AuditLoggerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

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

        return view('pages.admin.users.show', [
            'user' => $user,
            'roles' => Role::query()->where('guard_name', 'web')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $role = $request->string('role')->toString();
        $removesLastAdmin = $user->hasRole('platform_admin') && $role !== 'platform_admin';

        DB::transaction(function () use ($request, $user, $role, $removesLastAdmin): void {
            if ($removesLastAdmin && User::query()->whereHas('roles', fn ($query) => $query->where('name', 'platform_admin'))->lockForUpdate()->count() <= 1) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'At least one platform administrator must remain.');
            }

            $user->update($request->safe()->only(['name', 'email']));
            $user->syncRoles([$role]);

            $this->auditLogger->record(
                event: 'USER_ROLE_UPDATED',
                subject: (string) $user->getKey(),
                actor: (string) $request->user()->getAuthIdentifier(),
                risk: $role === 'platform_admin' ? 'high' : 'medium',
                metadata: ['role' => $role],
            );
        });

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated.');
    }
}
