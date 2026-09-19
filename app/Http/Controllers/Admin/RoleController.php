<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Contracts\AuditLoggerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function __construct(private AuditLoggerInterface $auditLogger) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount('users')
            ->with('permissions')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('pages.admin.roles.index', [
            'roles' => $roles,
            'permissions' => Permission::query()->where('guard_name', 'web')->orderBy('name')->get(),
            'search' => $search,
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $values = $request->validated();
        $role = DB::transaction(function () use ($request, $values): Role {
            $role = Role::query()->create(['name' => $values['name'], 'guard_name' => 'web']);
            $role->syncPermissions($values['permissions'] ?? []);
            $this->auditLogger->record(
                event: 'ROLE_CREATED',
                subject: (string) $role->getKey(),
                actor: (string) $request->user()->uuid,
                risk: 'high',
                metadata: ['role' => $role->name, 'permission_count' => count($values['permissions'] ?? [])],
            );

            return $role;
        });

        return redirect()->route('admin.roles.index')->with('success', "Role {$role->name} created.");
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->name === 'platform_admin') {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'The system role cannot be modified.');
        }

        $values = $request->validated();
        DB::transaction(function () use ($request, $role, $values): void {
            $role->update(['name' => $values['name']]);
            $role->syncPermissions($values['permissions'] ?? []);
            $this->auditLogger->record(
                event: 'ROLE_UPDATED',
                subject: (string) $role->getKey(),
                actor: (string) $request->user()->uuid,
                risk: 'high',
                metadata: ['role' => $role->name, 'permission_count' => count($values['permissions'] ?? [])],
            );
        });

        return redirect()->route('admin.roles.index')->with('success', "Role {$role->name} updated.");
    }
}
