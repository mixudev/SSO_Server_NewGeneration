<?php

namespace App\Http\Controllers\Oidc;

use App\Http\Controllers\Controller;
use App\Models\Identity\ApplicationCredential;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Vendor\LaravelAuthentication\Contracts\AuthenticationServiceInterface;
use Vendor\LaravelAuthentication\DTO\AuthenticationContext;

final class EndSessionController extends Controller
{
    public function __construct(private AuthenticationServiceInterface $authentication) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $redirect = $this->resolveRedirect($request);
        $this->authentication->logout(AuthenticationContext::fromRequest($request));

        return redirect()->to($redirect);
    }

    private function resolveRedirect(Request $request): string
    {
        $requested = $request->string('post_logout_redirect_uri')->toString();
        if ($requested === '') {
            return (string) config('authentication.redirects.logout', '/login');
        }

        $clientId = $request->string('client_id')->toString();
        if ($clientId === '') {
            abort(400, 'client_id is required with post_logout_redirect_uri.');
        }

        $allowed = ApplicationCredential::query()
            ->where('passport_client_id', $clientId)
            ->where('status', 'active')
            ->whereHas('application', function ($query): void {
                $query->where('status', 'active')
                    ->whereHas('organization', fn ($organization): mixed => $organization->where('status', 'active'));
            })
            ->whereHas('application.redirectUris', function ($query) use ($requested): void {
                $query->where('kind', 'logout')->where('uri', $requested);
            })
            ->exists();

        abort_unless($allowed, 400, 'post_logout_redirect_uri is not registered.');

        $state = $request->string('state')->toString();

        return $state === '' ? $requested : $requested.(str_contains($requested, '?') ? '&' : '?').'state='.rawurlencode($state);
    }
}
