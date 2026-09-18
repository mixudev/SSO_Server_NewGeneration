<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Applications\Services\ApplicationCredentialService;
use App\Http\Controllers\Controller;
use App\Models\Identity\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ApplicationCredentialController extends Controller
{
    public function __construct(private ApplicationCredentialService $credentials) {}

    public function issue(Request $request, Application $application): View
    {
        try {
            $result = $this->credentials->issue($application, (string) $request->user()->getAuthIdentifier());
        } catch (RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }

        return view('pages.admin.applications.credential', [
            'application' => $application->fresh('credential'),
            'result' => $result,
            'operation' => 'issued',
        ]);
    }

    public function rotate(Request $request, Application $application): View
    {
        try {
            $result = $this->credentials->rotate($application, (string) $request->user()->getAuthIdentifier());
        } catch (RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }

        return view('pages.admin.applications.credential', [
            'application' => $application->fresh('credential'),
            'result' => $result,
            'operation' => 'rotated',
        ]);
    }

    public function revoke(Request $request, Application $application): RedirectResponse
    {
        try {
            $this->credentials->revoke($application, (string) $request->user()->getAuthIdentifier());
        } catch (RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }

        return redirect()->route('admin.applications.show', $application)->with('success', 'Application credential revoked.');
    }
}
