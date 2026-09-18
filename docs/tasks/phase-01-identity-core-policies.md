# Phase 1 — Identity Core and Policies

## Scope

Establish the domain core for identity metadata, scopes, claims, key lifecycles, and security audit logging without coupling to OAuth 2.0 or SAML protocol specifics.

## Goals

1. Implement `Scope` and `Claim` registries with collision prevention.
2. Implement versioned claim policies with JSON Schema validation.
3. Build a cryptographic key lifecycle abstraction (active, rotated, revoked).
4. Implement tamper-evident audit logging for security events.
5. Provide adversarial tests for collision, injection, and expired key usage.

## Tasks

### 1.1 Scope and Claim Registries
- Paths: `app/Models/Identity/Scope.php`, `app/Models/Identity/Claim.php`, `database/factories/Identity/`, `database/migrations/`
- Target:
  - System and custom scopes with descriptions and default assignment rules.
  - Claim definitions with explicit value types (`string`, `boolean`, `array`, `json`) and database-level uniqueness for keys.
- Verification:
  ```bash
  php artisan test --filter=ScopeRegistryTest --compact
  php artisan test --filter=ClaimRegistryTest --compact
  ```

### 1.2 Claim Policy Engine
- Path: `app/Domain/Identity/Services/ClaimPolicyEngine.php`
- Contract: `app/Domain/Identity/Contracts/ClaimPolicyValidatorInterface.php`
- Target:
  - Map user attributes to requested scopes and application claim policies.
  - Enforce versioning on policies; prevent silent claim escalation.
- Verification:
  ```bash
  php artisan test --filter=ClaimPolicyEngineTest --compact
  ```

### 1.3 Cryptographic Key Abstraction
- Path: `app/Domain/Identity/Contracts/KeyManagerInterface.php`, `app/Infrastructure/Security/LocalRsaKeyManager.php`
- Target:
  - Key generation (RS256, 2048-bit minimum), public key extraction, kid calculation.
  - Active key resolution and rotation schedule without invalidating unexpired tokens.
- Verification:
  ```bash
  php artisan test --filter=KeyManagerTest --compact
  ```

### 1.4 Security Audit Subsystem
- Path: `app/Domain/Identity/Contracts/AuditLoggerInterface.php`, `app/Infrastructure/Audit/SecurityAuditLogger.php`
- Target:
  - Immutable audit logs with actor ID, IP, user-agent, action code, and JSON payload.
  - Automatic redaction of sensitive keys (`password`, `secret`, `private_key`).
- Verification:
  ```bash
  php artisan test --filter=SecurityAuditLoggerTest --compact
  ```

### 1.5 Adversarial Test Suite
- Path: `tests/Feature/IdentityCoreAdversarialTest.php`
- Target:
  - Scope collision, case sensitivity attacks, claim override attempts, and expired key signing requests.
- Verification:
  ```bash
  php artisan test tests/Feature/IdentityCoreAdversarialTest.php --compact
  ```

## Acceptance Criteria

- Zero OAuth 2.0 or SAML dependencies imported in `app/Domain/Identity`.
- Key rotation produces compliant JWK public representations.
- All tests pass with 100% assertions satisfied.
