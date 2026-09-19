<?php

namespace App\Domain\Applications\Services;

use App\Domain\Identity\Contracts\AuditLoggerInterface;
use App\Domain\OAuth\Contracts\OAuthClientRepositoryInterface;
use App\Domain\OAuth\Data\OAuthClientData;
use App\Models\Identity\Application;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class ApplicationCredentialService
{
    public function __construct(
        private OAuthClientRepositoryInterface $clients,
        private AuditLoggerInterface $auditLogger,
    ) {}

    /** @return array{client_id: string, client_secret: ?string, confidential: bool, generation: int} */
    public function issue(Application $application, string $actorId): array
    {
        return DB::transaction(function () use ($application, $actorId): array {
            $application = Application::query()
                ->with(['organization', 'redirectUris'])
                ->lockForUpdate()
                ->findOrFail($application->getKey());

            $this->assertIssuable($application);
            if ($application->credential()->exists()) {
                throw new RuntimeException('Application already has credentials.');
            }

            $client = $this->createClient($application);
            $credential = $application->credential()->create([
                'passport_client_id' => $client->clientId,
                'status' => 'active',
                'generation' => 1,
            ]);

            $this->auditLogger->record(
                event: 'APPLICATION_CREDENTIAL_ISSUED',
                organizationId: (string) $application->organization_id,
                applicationId: (string) $application->getKey(),
                subject: (string) $credential->getKey(),
                actor: $actorId,
                risk: 'high',
                metadata: ['client_type' => $application->client_type, 'generation' => 1],
            );

            return $this->response($client, $credential->generation);
        });
    }

    /** @return array{client_id: string, client_secret: ?string, confidential: bool, generation: int} */
    public function rotate(Application $application, string $actorId): array
    {
        return DB::transaction(function () use ($application, $actorId): array {
            $application = Application::query()->with('organization')->lockForUpdate()->findOrFail($application->getKey());
            $credential = $application->credential()->lockForUpdate()->firstOrFail();
            $client = $this->clients->find($credential->passport_client_id);
            if ($client === null || $client->revoked || $credential->status !== 'active') {
                throw new RuntimeException('Application credentials are unavailable.');
            }
            $this->assertIssuable($application);

            if (! $client->confidential) {
                throw new RuntimeException('Public applications do not have a secret to rotate.');
            }

            $client = $this->clients->regenerateSecret($credential->passport_client_id);
            $credential->increment('generation');
            $credential->refresh();

            $this->auditLogger->record(
                event: 'APPLICATION_CREDENTIAL_ROTATED',
                organizationId: (string) $application->organization_id,
                applicationId: (string) $application->getKey(),
                subject: (string) $credential->getKey(),
                actor: $actorId,
                risk: 'high',
                metadata: ['generation' => $credential->generation],
            );

            return $this->response($client, $credential->generation);
        });
    }

    /** @return array{client_id: string, client_secret: ?string, confidential: bool, generation: int} */
    public function reactivate(Application $application, string $actorId): array
    {
        return DB::transaction(function () use ($application, $actorId): array {
            $application = Application::query()->with(['organization', 'redirectUris'])->lockForUpdate()->findOrFail($application->getKey());
            $credential = $application->credential()->lockForUpdate()->firstOrFail();
            $this->assertIssuable($application);
            $client = $this->createClient($application);
            $credential->update([
                'passport_client_id' => $client->clientId,
                'status' => 'active',
                'generation' => $credential->generation + 1,
                'revoked_at' => null,
            ]);

            $this->auditLogger->record(
                event: 'APPLICATION_CREDENTIAL_REACTIVATED',
                organizationId: (string) $application->organization_id,
                applicationId: (string) $application->getKey(),
                subject: (string) $credential->getKey(),
                actor: $actorId,
                risk: 'high',
                metadata: ['generation' => $credential->generation],
            );

            return $this->response($client, $credential->generation);
        });
    }

    public function revoke(Application $application, string $actorId): void
    {
        DB::transaction(function () use ($application, $actorId): void {
            $application = Application::query()->lockForUpdate()->findOrFail($application->getKey());
            $credential = $application->credential()->lockForUpdate()->firstOrFail();
            $client = $this->clients->find($credential->passport_client_id);
            if ($client !== null && ! $client->revoked) {
                $this->clients->revoke($credential->passport_client_id);
            }
            $credential->update(['status' => 'revoked', 'revoked_at' => now()]);
            $this->auditLogger->record(
                event: 'APPLICATION_CREDENTIAL_REVOKED',
                organizationId: (string) $application->organization_id,
                applicationId: (string) $application->getKey(),
                subject: (string) $credential->getKey(),
                actor: $actorId,
                risk: 'high',
            );
        });
    }

    private function assertIssuable(Application $application): void
    {
        if ($application->status !== 'active' || $application->organization?->status !== 'active') {
            throw new RuntimeException('Only active applications in active organizations can receive credentials.');
        }
        if (! in_array($application->client_type, ['confidential_web', 'public_spa', 'native'], true)) {
            throw new RuntimeException('Application client type is unsupported.');
        }
    }

    private function createClient(Application $application): OAuthClientData
    {
        $redirectUris = $application->redirectUris->pluck('uri')->values()->all();

        return $this->clients->createAuthorizationCodeClient(
            $application->name,
            $redirectUris,
            $application->client_type === 'confidential_web',
        );
    }

    /** @return array{client_id: string, client_secret: ?string, confidential: bool, generation: int} */
    private function response(OAuthClientData $client, int $generation): array
    {
        return [
            'client_id' => $client->clientId,
            'client_secret' => $client->clientSecret,
            'confidential' => $client->confidential,
            'generation' => $generation,
        ];
    }
}
