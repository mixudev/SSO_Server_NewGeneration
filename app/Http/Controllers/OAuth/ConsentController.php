<?php

namespace App\Http\Controllers\OAuth;

use App\Domain\OAuth\Enums\AuthorizationTransactionStatus;
use App\Http\Controllers\Controller;
use App\Infrastructure\OAuth\PassportAuthorizationCodeIssuer;
use App\Models\OAuth\AuthorizationTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ConsentController extends Controller
{
    public function __construct(private PassportAuthorizationCodeIssuer $codeIssuer) {}

    public function approve(Request $request, AuthorizationTransaction $transaction): RedirectResponse|SymfonyResponse
    {
        if ((string) $transaction->user_id !== (string) $request->user()->getAuthIdentifier()) {
            return response('Invalid authorization transaction.', Response::HTTP_BAD_REQUEST);
        }

        if ($transaction->status !== AuthorizationTransactionStatus::Pending) {
            return response('Invalid authorization transaction.', Response::HTTP_BAD_REQUEST);
        }

        if ($transaction->expires_at->isPast()) {
            $transaction->update(['status' => AuthorizationTransactionStatus::Expired]);

            return response('Authorization transaction expired.', Response::HTTP_BAD_REQUEST);
        }

        $sessionData = $request->session()->pull('oauth.authorization.'.$transaction->getKey());
        if (! is_array($sessionData)
            || ! is_string($sessionData['redirect_uri'] ?? null)
            || ! is_string($sessionData['state'] ?? null)
            || ! hash_equals($transaction->redirect_uri_hash, hash('sha256', $sessionData['redirect_uri']))
            || ($transaction->state_hash !== null
                && ! hash_equals($transaction->state_hash, hash('sha256', $sessionData['state'])))
        ) {
            return response('Invalid authorization transaction.', Response::HTTP_BAD_REQUEST);
        }

        try {
            return DB::transaction(function () use ($transaction, $sessionData): RedirectResponse|SymfonyResponse {
                $lockedTransaction = AuthorizationTransaction::query()
                    ->whereKey($transaction->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedTransaction->status !== AuthorizationTransactionStatus::Pending
                    || $lockedTransaction->expires_at->isPast()
                ) {
                    throw new RuntimeException('Authorization transaction is no longer pending.');
                }

                $lockedTransaction->update([
                    'status' => AuthorizationTransactionStatus::Approved,
                    'consented_at' => now(),
                ]);

                return $this->codeIssuer->issue(
                    $lockedTransaction,
                    $sessionData['redirect_uri'],
                    $sessionData['state'],
                );
            });
        } catch (RuntimeException) {
            return response('Invalid authorization transaction.', Response::HTTP_BAD_REQUEST);
        }
    }
}
