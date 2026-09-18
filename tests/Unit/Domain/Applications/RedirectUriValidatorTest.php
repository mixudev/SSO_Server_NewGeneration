<?php

namespace Tests\Unit\Domain\Applications;

use App\Domain\Applications\Services\RedirectUriValidator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RedirectUriValidatorTest extends TestCase
{
    private RedirectUriValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new RedirectUriValidator;
    }

    public function test_canonicalizes_scheme_and_host_without_changing_path_or_query(): void
    {
        $this->assertSame(
            'https://trusted.example/callback?mode=login',
            $this->validator->canonicalize('HTTPS://TRUSTED.EXAMPLE/callback?mode=login'),
        );
    }

    public function test_exact_matching_rejects_open_redirect_variants(): void
    {
        $registered = 'https://trusted.example/callback';

        foreach ([
            'https://trusted.example/callback.evil',
            'https://user@evil.example/callback',
            'https://evil.example/?redirect=https://trusted.example',
            'https://trusted.example/%2e%2e/evil',
            'https://trusted.example/callback%2f..%2fevil',
            'https://trusted.example/callback#fragment',
            'https://*.trusted.example/callback',
        ] as $requested) {
            $this->assertFalse($this->validator->matches($registered, $requested), $requested);
        }
    }

    public function test_rejects_invalid_non_loopback_http_and_malformed_uris(): void
    {
        foreach ([
            'http://trusted.example/callback',
            'https://trusted.example/*',
            'https://trusted.example/callback#fragment',
            'https://trusted.example/callback with-space',
            'not-a-uri',
        ] as $uri) {
            try {
                $this->validator->canonicalize($uri);
                $this->fail('Invalid redirect URI was accepted: '.$uri);
            } catch (InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_allows_http_only_for_loopback_hosts(): void
    {
        $this->assertSame(
            'http://127.0.0.1:8000/callback',
            $this->validator->canonicalize('http://127.0.0.1:8000/callback'),
        );
    }
}
