<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Services\SecurityEventQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request, SecurityEventQuery $events): View
    {
        return view('pages.admin.audit.index', [
            'events' => $events->paginate($request),
        ]);
    }
}
