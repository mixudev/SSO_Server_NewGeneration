<?php

namespace App\Domain\OAuth\Data;

final readonly class OAuthClientData
{
    public function __construct(
        public string $clientId,
        public ?string $clientSecret,
        public bool $confidential,
        public bool $revoked,
    ) {}
}
