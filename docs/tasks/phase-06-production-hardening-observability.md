# Phase 6 — Production Hardening and Observability

## Scope

Make security-critical identity flows operable in production with bounded abuse controls, recovery procedures, and privacy-safe observability.

## Tasks

### 6.1 Rate limits
- Paths: `app/Providers/AppServiceProvider.php`, `routes/`, `tests/Feature/RateLimitTest.php`.
- Define named limits for login, authorization, token, revocation, discovery, and metadata endpoints; key limits by the safest available client/IP identity.
- TDD cases: threshold, retry-after, independent clients, and proxy spoofing.
- Verify: `php artisan test tests/Feature/RateLimitTest.php --compact`.

### 6.2 Backup and key recovery
- Confirm package version with `composer show spatie/laravel-backup`.
- Paths: `config/backup.php`, `app/Console/Commands/`, `tests/Feature/BackupHealthTest.php`.
- Configure encrypted database/filesystem backups, retention, failure notifications, and a key restoration runbook. Never store secrets in the repository.
- Verify: `php artisan backup:run --help`, then the focused test in a configured local environment.

### 6.3 Health checks and metrics
- Paths: `routes/health.php`, `app/Http/Controllers/HealthController.php`, `tests/Feature/HealthCheckTest.php`.
- Expose dependency health and counters for authorization failures, token issuance, latency, and key status without user data or tokens.
- Verify: `php artisan test tests/Feature/HealthCheckTest.php --compact`.

### 6.4 Audit retention and export
- Paths: `app/Console/Commands/RotateAuditLogs.php`, `app/Infrastructure/Audit/`, `tests/Feature/AuditRetentionTest.php`.
- Apply retention, tamper evidence, redaction, access control, and bounded export; fail closed when the export destination is unavailable.
- Verify: `php artisan test tests/Feature/AuditRetentionTest.php --compact`.

### 6.5 Disaster recovery drill
- Paths: `docs/15-backup-and-disaster-recovery.md`, `tests/Feature/DisasterRecoveryTest.php`.
- Test restore of database, application configuration, and public/private key continuity in an isolated environment. Record RPO/RTO results without committing credentials.
- Verify: `php artisan test tests/Feature/DisasterRecoveryTest.php --compact`.

## Acceptance Criteria

- Abuse controls have tested thresholds and do not trust spoofable proxy headers.
- Backups are encrypted, restorable, monitored, and documented.
- Metrics and logs contain no secrets or raw tokens.
- Recovery drill meets documented RPO/RTO targets.
- Full tests, Pint, and `git diff --check` pass.
