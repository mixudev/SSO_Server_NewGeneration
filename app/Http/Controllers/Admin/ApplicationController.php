<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Applications\Services\ApplicationWizardIntegrityValidator;
use App\Domain\Applications\Services\RedirectUriValidator;
use App\Domain\Identity\Contracts\AuditLoggerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreApplicationRequest;
use App\Http\Requests\Admin\UpdateApplicationRequest;
use App\Models\Identity\Application;
use App\Models\Identity\Claim;
use App\Models\Identity\Organization;
use App\Models\Identity\Scope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(
        private AuditLoggerInterface $auditLogger,
        private ApplicationWizardIntegrityValidator $wizardIntegrityValidator,
    ) {}

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

    public function wizardBasic(Request $request): View|RedirectResponse
    {
        return view('pages.admin.applications.wizard', [
            'step' => 'basic',
            'wizard' => $request->session()->get('application_wizard', []),
        ]);
    }

    public function wizardBasicStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'organization_id' => ['required', 'string', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
        $request->session()->put('application_wizard', ['basic' => $data]);

        return redirect()->route('admin.applications.wizard.protocol');
    }

    public function wizardProtocol(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('application_wizard.basic')) {
            return redirect()->route('admin.applications.wizard.basic');
        }

        return view('pages.admin.applications.wizard', [
            'step' => 'protocol',
            'wizard' => $request->session()->get('application_wizard'),
        ]);
    }

    public function wizardProtocolStore(Request $request): RedirectResponse
    {
        if (! $request->session()->has('application_wizard.basic')) {
            return redirect()->route('admin.applications.wizard.basic');
        }
        $data = $request->validate([
            'protocol_mode' => ['required', 'in:oauth2,oidc'],
            'client_type' => ['required', 'in:confidential_web,public_spa,native'],
        ]);
        $request->session()->put('application_wizard.protocol', $data);

        return redirect()->route('admin.applications.wizard.redirect');
    }

    public function wizardRedirect(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('application_wizard.protocol')) {
            return redirect()->route('admin.applications.wizard.basic');
        }

        return view('pages.admin.applications.wizard', [
            'step' => 'redirect',
            'wizard' => $request->session()->get('application_wizard'),
        ]);
    }

    public function wizardRedirectStore(Request $request): RedirectResponse
    {
        if (! $request->session()->has('application_wizard.protocol')) {
            return redirect()->route('admin.applications.wizard.basic');
        }
        $data = $request->validate([
            'redirect_uris' => ['required', 'array', 'min:1', 'max:20'],
            'redirect_uris.*' => ['required', 'string', 'max:2048'],
        ]);
        $canonicalUris = [];
        $validator = app(RedirectUriValidator::class);
        foreach ($data['redirect_uris'] as $index => $uri) {
            try {
                $canonicalUris[] = $validator->canonicalize($uri);
            } catch (\InvalidArgumentException) {
                throw ValidationException::withMessages([
                    "redirect_uris.{$index}" => 'Redirect URI is invalid.',
                ]);
            }
        }
        if (count($canonicalUris) !== count(array_unique($canonicalUris))) {
            throw ValidationException::withMessages([
                'redirect_uris' => 'Redirect URIs must be unique.',
            ]);
        }
        $request->session()->put('application_wizard.redirect_uris', $canonicalUris);

        return redirect()->route('admin.applications.wizard.scopes');
    }

    public function wizardScopes(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('application_wizard.redirect_uris')) {
            return redirect()->route('admin.applications.wizard.basic');
        }

        return view('pages.admin.applications.wizard', [
            'step' => 'scopes',
            'wizard' => $request->session()->get('application_wizard'),
            'scopes' => Scope::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function wizardScopesStore(Request $request): RedirectResponse
    {
        if (! $request->session()->has('application_wizard.redirect_uris')) {
            return redirect()->route('admin.applications.wizard.basic');
        }
        $data = $request->validate([
            'scope_ids' => ['present', 'array'],
            'scope_ids.*' => ['required', 'string'],
        ]);
        $scopeIds = array_values(array_unique($data['scope_ids']));
        if (count($scopeIds) !== count($data['scope_ids'])) {
            throw ValidationException::withMessages(['scope_ids' => 'Scopes must be unique.']);
        }
        $validScopeIds = Scope::query()->where('status', 'active')->whereKey($scopeIds)->pluck('id')->all();
        if (count($validScopeIds) !== count($scopeIds)) {
            throw ValidationException::withMessages(['scope_ids' => 'One or more scopes are unknown or inactive.']);
        }
        $request->session()->put('application_wizard.scope_ids', $scopeIds);

        return redirect()->route('admin.applications.wizard.claims');
    }

    public function wizardClaims(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('application_wizard.scope_ids')) {
            return redirect()->route('admin.applications.wizard.basic');
        }

        return view('pages.admin.applications.wizard', [
            'step' => 'claims',
            'wizard' => $request->session()->get('application_wizard'),
            'claims' => Claim::query()->where('status', 'active')->orderBy('key')->get(),
        ]);
    }

    public function wizardClaimsStore(Request $request): RedirectResponse
    {
        if (! $request->session()->has('application_wizard.scope_ids')) {
            return redirect()->route('admin.applications.wizard.basic');
        }
        $data = $request->validate([
            'claim_keys' => ['present', 'array'],
            'claim_keys.*' => ['required', 'string', 'max:191'],
            'claim_policy_version' => ['required', 'integer', 'min:1'],
        ]);
        $claimKeys = array_values(array_unique($data['claim_keys']));
        if (count($claimKeys) !== count($data['claim_keys'])) {
            throw ValidationException::withMessages(['claim_keys' => 'Claims must be unique.']);
        }
        $validClaimKeys = Claim::query()->where('status', 'active')->whereIn('key', $claimKeys)->pluck('key')->all();
        if (count($validClaimKeys) !== count($claimKeys)) {
            throw ValidationException::withMessages(['claim_keys' => 'One or more claims are unknown or inactive.']);
        }
        $request->session()->put('application_wizard.claims', [
            'keys' => $claimKeys,
            'version' => $data['claim_policy_version'],
        ]);

        return redirect()->route('admin.applications.wizard.security');
    }

    public function wizardSecurity(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('application_wizard.claims')) {
            return redirect()->route('admin.applications.wizard.basic');
        }

        return view('pages.admin.applications.wizard', [
            'step' => 'security',
            'wizard' => $request->session()->get('application_wizard'),
        ]);
    }

    public function wizardSecurityStore(Request $request): RedirectResponse
    {
        if (! $request->session()->has('application_wizard.claims')) {
            return redirect()->route('admin.applications.wizard.basic');
        }
        $data = $request->validate([
            'consent_policy' => ['required', 'in:explicit,implicit,none'],
            'session_max_age' => ['required', 'integer', 'min:300', 'max:86400'],
            'session_idle_timeout' => ['required', 'integer', 'min:60', 'max:900'],
        ]);
        $protocol = $request->session()->get('application_wizard.protocol');
        if ($protocol['client_type'] === 'public_spa' && $data['session_idle_timeout'] > 600) {
            throw ValidationException::withMessages([
                'session_idle_timeout' => 'Public SPA sessions cannot exceed 600 seconds of idle time.',
            ]);
        }
        if ($data['session_idle_timeout'] > $data['session_max_age']) {
            throw ValidationException::withMessages([
                'session_idle_timeout' => 'Idle timeout cannot exceed maximum session age.',
            ]);
        }
        $request->session()->put('application_wizard.security', $data);

        return redirect()->route('admin.applications.wizard.review');
    }

    public function wizardReview(Request $request): View|RedirectResponse
    {
        $wizard = $request->session()->get('application_wizard', []);
        if (! is_array($wizard)) {
            return redirect()->route('admin.applications.wizard.basic');
        }

        try {
            $wizard = $this->wizardIntegrityValidator->validate($wizard);
        } catch (ValidationException $exception) {
            return redirect()
                ->route('admin.applications.wizard.basic')
                ->withErrors($exception->errors());
        }

        return view('pages.admin.applications.wizard', [
            'step' => 'review',
            'wizard' => $wizard,
            'selectedScopes' => Scope::query()->whereKey($wizard['scope_ids'])->orderBy('name')->get(),
            'selectedClaims' => Claim::query()->whereIn('key', $wizard['claims']['keys'])->orderBy('key')->get(),
        ]);
    }

    public function wizardComplete(Request $request): RedirectResponse
    {
        $wizard = $request->session()->get('application_wizard', []);
        if (! is_array($wizard)) {
            return redirect()->route('admin.applications.wizard.basic');
        }

        $wizard = $this->wizardIntegrityValidator->validate($wizard);
        $application = DB::transaction(function () use ($wizard, $request): Application {
            $organizationIsActive = Organization::query()
                ->whereKey($wizard['basic']['organization_id'])
                ->where('status', 'active')
                ->exists();
            if (! $organizationIsActive) {
                throw ValidationException::withMessages([
                    'wizard' => 'The selected organization is no longer active.',
                ]);
            }

            $activeScopeIds = Scope::query()
                ->where('status', 'active')
                ->whereKey($wizard['scope_ids'])
                ->pluck('id')
                ->all();
            $activeClaimKeys = Claim::query()
                ->where('status', 'active')
                ->whereIn('key', $wizard['claims']['keys'])
                ->pluck('key')
                ->all();
            if (count($activeScopeIds) !== count($wizard['scope_ids']) || count($activeClaimKeys) !== count($wizard['claims']['keys'])) {
                throw ValidationException::withMessages([
                    'registry' => 'A selected scope or claim is no longer active.',
                ]);
            }
            $application = Application::query()->create([
                ...$wizard['basic'],
                ...$wizard['protocol'],
                'status' => 'draft',
                'consent_policy' => $wizard['security']['consent_policy'],
                'session_policy_json' => [
                    'claims' => $activeClaimKeys,
                    'max_age' => $wizard['security']['session_max_age'],
                    'idle_timeout' => $wizard['security']['session_idle_timeout'],
                ],
                'claim_policy_version' => $wizard['claims']['version'],
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);
            $application->scopes()->attach($activeScopeIds, [
                'allowed' => true,
                'consent_required' => true,
            ]);
            foreach ($wizard['redirect_uris'] as $uri) {
                $application->redirectUris()->create([
                    'uri' => $uri,
                    'uri_hash' => hash('sha256', $uri),
                    'kind' => 'login',
                ]);
            }

            return $application;
        });
        $request->session()->forget('application_wizard');

        return redirect()->route('admin.applications.index')->with('success', "Application {$application->name} created.");
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

    public function destroy(Application $application, Request $request): RedirectResponse
    {
        if ($application->status !== 'draft') {
            return back()->withErrors(['application' => 'Only draft applications can be deleted.']);
        }

        DB::transaction(function () use ($application, $request): void {
            $applicationId = $application->getKey();
            $application->delete();

            $this->auditLogger->record(
                event: 'APPLICATION_DELETED',
                applicationId: $applicationId,
                actor: (string) $request->user()->getAuthIdentifier(),
                risk: 'high',
            );
        });

        return redirect()->route('admin.applications.index')->with('success', 'Application deleted.');
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
