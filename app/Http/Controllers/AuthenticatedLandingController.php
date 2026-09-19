<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AuthenticatedLandingController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return $request->user()->can('admin.dashboard.view')
            ? redirect()->route('admin.dashboard')
            : redirect()->route('sso.portal');
    }
}
