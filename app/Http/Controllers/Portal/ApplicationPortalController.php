<?php

namespace App\Http\Controllers\Portal;

use App\Domain\Applications\Services\ApplicationAccessEvaluator;
use App\Http\Controllers\Controller;
use App\Models\Identity\Application;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ApplicationPortalController extends Controller
{
    public function __construct(private ApplicationAccessEvaluator $accessEvaluator) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $applications = Application::query()
            ->with('organization')
            ->where('status', 'active')
            ->whereHas('organization', fn ($query) => $query->where('status', 'active'))
            ->whereHas('credential', fn ($query) => $query->where('status', 'active'))
            ->whereHas('userAccess', fn ($query) => $query->where('user_id', $user->getKey())->where('status', 'active'))
            ->orderBy('name')
            ->limit(100)
            ->get();

        $applications = $applications->filter(
            fn (Application $application): bool => $this->accessEvaluator->canLaunch($user, $application),
        )->values();

        return view('pages.portal.index', compact('applications'));
    }
}
