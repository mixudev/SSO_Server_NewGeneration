<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Sessions\Services\SessionInspector;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function index(SessionInspector $inspector): View
    {
        return view('pages.admin.sessions.index', [
            'sessions' => $inspector->paginate(),
        ]);
    }
}
