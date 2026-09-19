<?php

namespace App\Domain\OAuth\Data;

use InvalidArgumentException;

final readonly class AuthorizationRequestData
{
    /**
     * @param  list<string>  $parameters
     */
    public function __construct(
        public string $clientId,
        public string $redirectUri,
        public string $responseType,
        public string $scope,
        public string $state,
        public string $codeChallenge,
        public string $codeChallengeMethod,
        public ?string $nonce = null,
        public array $parameters = [],
    ) {
        if ($this->clientId === '' || $this->redirectUri === '' || $this->responseType === ''
            || $this->scope === '' || $this->state === '' || $this->codeChallenge === '') {
            throw new InvalidArgumentException('Authorization request is invalid.');
        }
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function fromArray(array $parameters): self
    {
        $required = ['client_id', 'redirect_uri', 'response_type', 'scope', 'state', 'code_challenge', 'code_challenge_method'];

        foreach ($required as $name) {
            if (! array_key_exists($name, $parameters) || ! is_string($parameters[$name])) {
                throw new InvalidArgumentException('Authorization request is invalid.');
            }
        }

        return new self(
            clientId: $parameters['client_id'],
            redirectUri: $parameters['redirect_uri'],
            responseType: $parameters['response_type'],
            scope: $parameters['scope'],
            state: $parameters['state'],
            codeChallenge: $parameters['code_challenge'],
            codeChallengeMethod: $parameters['code_challenge_method'],
            nonce: isset($parameters['nonce']) && is_string($parameters['nonce']) ? $parameters['nonce'] : null,
            parameters: array_keys($parameters),
        );
    }

    public static function fromQueryString(string $query): self
    {
        $parameters = [];

        foreach ($query === '' ? [] : explode('&', $query) as $pair) {
            [$rawName, $rawValue] = array_pad(explode('=', $pair, 2), 2, '');
            $name = urldecode($rawName);

            if ($name === '' || array_key_exists($name, $parameters)) {
                throw new InvalidArgumentException('Authorization request contains duplicate parameters.');
            }

            $parameters[$name] = urldecode($rawValue);
        }

        return self::fromArray($parameters);
    }
}
