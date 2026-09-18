<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Contracts\AuditLoggerInterface;
use App\Domain\Identity\Contracts\KeyManagerInterface;
use App\Http\Controllers\Controller;
use App\Models\Identity\SigningKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KeyController extends Controller
{
    public function __construct(
        private KeyManagerInterface $keys,
        private AuditLoggerInterface $auditLogger,
    ) {}

    public function index(): View
    {
        return view('pages.admin.keys.index', [
            'keys' => SigningKey::query()->select([
                'id', 'kid', 'algorithm', 'status', 'activated_at', 'retired_at', 'created_at',
            ])->latest('created_at')->paginate(20),
        ]);
    }

    public function rotate(Request $request): RedirectResponse
    {
        $key = $this->keys->rotate();
        $this->auditLogger->record(
            event: 'SIGNING_KEY_ROTATED',
            subject: $key->kid,
            actor: (string) $request->user()->getAuthIdentifier(),
            risk: 'high',
            metadata: ['algorithm' => $key->algorithm],
        );

        return redirect()->route('admin.keys.index')->with('success', 'Signing key rotated.');
    }

    public function revoke(Request $request, SigningKey $key): RedirectResponse
    {
        if ($key->status === 'active' && SigningKey::query()->where('algorithm', $key->algorithm)->where('status', 'active')->count() <= 1) {
            abort(422, 'The last active signing key cannot be revoked.');
        }

        $key->update(['status' => 'retired', 'retired_at' => now()]);
        $this->auditLogger->record(
            event: 'SIGNING_KEY_REVOKED',
            subject: $key->kid,
            actor: (string) $request->user()->getAuthIdentifier(),
            risk: 'high',
            metadata: ['algorithm' => $key->algorithm],
        );

        return redirect()->route('admin.keys.index')->with('success', 'Signing key retired.');
    }
}
