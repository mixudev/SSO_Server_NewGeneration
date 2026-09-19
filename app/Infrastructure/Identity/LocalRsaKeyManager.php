<?php

namespace App\Infrastructure\Identity;

use App\Domain\Identity\Contracts\KeyManagerInterface;
use App\Models\Identity\SigningKey;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class LocalRsaKeyManager implements KeyManagerInterface
{
    public function generate(): SigningKey
    {
        $keyPair = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($keyPair === false || ! openssl_pkey_export($keyPair, $privateKey)) {
            throw new RuntimeException('Unable to generate RSA signing key.');
        }

        $details = openssl_pkey_get_details($keyPair);

        if ($details === false || ! isset($details['key'])) {
            throw new RuntimeException('Unable to extract RSA public key.');
        }

        $publicKey = $details['key'];
        $kid = substr(hash('sha256', $publicKey), 0, 32);

        $signingKey = new SigningKey([
            'kid' => $kid,
            'algorithm' => 'RS256',
            'public_key' => $publicKey,
            'status' => 'active',
            'active_slot' => 1,
            'activated_at' => now(),
        ]);
        $signingKey->forceFill(['private_key' => $privateKey])->save();

        return $signingKey;
    }

    public function active(): SigningKey
    {
        return SigningKey::query()
            ->where('algorithm', 'RS256')
            ->where('status', 'active')
            ->latest('activated_at')
            ->firstOrFail();
    }

    public function verificationKeys(): Collection
    {
        return SigningKey::query()
            ->where('algorithm', 'RS256')
            ->whereIn('status', ['active', 'retired'])
            ->where(function ($query): void {
                $query->where('status', 'active')
                    ->orWhere('retired_at', '>=', now()->subHours(2));
            })
            ->orderByDesc('activated_at')
            ->get();
    }

    public function rotate(): SigningKey
    {
        return DB::transaction(function (): SigningKey {
            SigningKey::query()
                ->where('algorithm', 'RS256')
                ->where('status', 'active')
                ->update([
                    'status' => 'retired',
                    'active_slot' => null,
                    'retired_at' => now(),
                ]);

            return $this->generate();
        });
    }
}
