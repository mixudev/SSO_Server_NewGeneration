<?php

namespace App\Http\Controllers;

use App\Domain\Identity\Contracts\KeyManagerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

final class HealthController extends Controller
{
    public function ready(KeyManagerInterface $keys): JsonResponse
    {
        $checks = [
            'database' => $this->databaseIsReady() ? 'ok' : 'failed',
            'signing_key' => $this->signingKeyIsReady($keys) ? 'ok' : 'failed',
        ];
        $healthy = ! in_array('failed', $checks, true);

        return response()->json([
            'status' => $healthy ? 'ok' : 'failed',
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    private function databaseIsReady(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function signingKeyIsReady(KeyManagerInterface $keys): bool
    {
        try {
            $keys->active();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
