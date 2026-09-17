<?php

namespace App\Domain\Applications\Services;

use InvalidArgumentException;

final class RedirectUriValidator
{
    public function canonicalize(string $uri): string
    {
        if ($uri === '' || preg_match('/[\x00-\x20\x7f]/', $uri) === 1) {
            throw new InvalidArgumentException('Redirect URI is invalid.');
        }

        if (str_contains($uri, '*') || str_contains($uri, '#')) {
            throw new InvalidArgumentException('Redirect URI is invalid.');
        }

        $parts = parse_url($uri);

        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'], $parts['path'])) {
            throw new InvalidArgumentException('Redirect URI is invalid.');
        }

        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);
        $port = $parts['port'] ?? null;

        if ($scheme !== 'https' && ! ($scheme === 'http' && $this->isLoopback($host))) {
            throw new InvalidArgumentException('Redirect URI is invalid.');
        }

        if (isset($parts['user']) || isset($parts['pass']) || $this->containsEncodedPathSeparator($parts['path'])) {
            throw new InvalidArgumentException('Redirect URI is invalid.');
        }

        if ($this->containsDotSegment($parts['path']) || ($port !== null && ! $this->isValidPort($port))) {
            throw new InvalidArgumentException('Redirect URI is invalid.');
        }

        if (($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80)) {
            $port = null;
        }

        return $scheme.'://'.$host.($port === null ? '' : ':'.$port).
            $parts['path'].(isset($parts['query']) ? '?'.$parts['query'] : '');
    }

    public function matches(string $registeredUri, string $requestedUri): bool
    {
        try {
            return hash_equals($this->canonicalize($registeredUri), $this->canonicalize($requestedUri));
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    private function isLoopback(string $host): bool
    {
        return in_array($host, ['localhost', '127.0.0.1', '[::1]', '::1'], true);
    }

    private function containsEncodedPathSeparator(string $path): bool
    {
        return preg_match('/%(?:2f|2F|5c|5C)/', $path) === 1;
    }

    private function containsDotSegment(string $path): bool
    {
        return preg_match('~(?:^|/)(?:\.{1,2})(?:/|$)~', rawurldecode($path)) === 1;
    }

    private function isValidPort(int $port): bool
    {
        return $port >= 1 && $port <= 65535;
    }
}
