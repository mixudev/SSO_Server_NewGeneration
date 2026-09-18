<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreApplicationRequest;
use App\Http\Requests\Admin\UpdateApplicationRequest;
use App\Models\Identity\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        if (! in_array($status, ['', 'draft', 'active', 'suspended', 'revoked'], true)) {
            $status = '';
        }

        $applications = Application::query()
            ->with('organization')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.applications.index', compact('applications', 'search', 'status'));
    }

    public function show(Application $application): View
    {
        $application->load(['organization', 'redirectUris']);

        return view('pages.admin.applications.show', compact('application'));
    }

    public function update(UpdateApplicationRequest $request, Application $application): RedirectResponse
    {
        DB::transaction(function () use ($request, $application): void {
            $application->update([
                ...$request->safe()->except('canonical_redirect_uris'),
                'updated_by' => $request->user()->id,
            ]);
            $application->redirectUris()->delete();

            foreach ($request->input('canonical_redirect_uris', []) as $uri) {
                $application->redirectUris()->create([
                    'uri' => $uri,
                    'uri_hash' => hash('sha256', $uri),
                    'kind' => 'login',
                ]);
            }
        });

        return redirect()->route('admin.applications.show', $application)->with('success', "Application {$application->name} updated.");
    }

    public function store(StoreApplicationRequest $request): RedirectResponse
    {
        $application = DB::transaction(function () use ($request): Application {
            $application = Application::query()->create([
                ...$request->safe()->except('canonical_redirect_uris'),
                'status' => 'draft',
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            foreach ($request->input('canonical_redirect_uris', []) as $uri) {
                $application->redirectUris()->create([
                    'uri' => $uri,
                    'uri_hash' => hash('sha256', $uri),
                    'kind' => 'login',
                ]);
            }

            return $application;
        });

        return redirect()->route('admin.applications.index')->with('success', "Application {$application->name} created.");
    }
}
