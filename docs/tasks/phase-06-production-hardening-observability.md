# Phase 6 — Production Hardening and Observability

## Scope

Make security-critical identity flows operable in production with bounded abuse controls, recovery procedures, and privacy-safe observability.

## Tasks

### 6.1 Rate limits
- Named limits are registered in `AppServiceProvider`: public OIDC metadata uses 60 requests/minute per IP; authorization and revocation use 30 requests/minute per authenticated user or IP fallback.
- Authorization, revocation, discovery, JWKS, and UserInfo routes use the named middleware. Passport-owned `/oauth/token` is also wrapped with `throttle:oauth-token` after verifying Passport 13.8 route ownership.
- Coverage: `tests/Feature/RateLimitTest.php` plus OAuth/OIDC tests verify the thresholds and endpoint behavior.
- Token endpoint route verification: `php artisan route:list --path=oauth/token -v` must show both Passport's default `throttle` and `throttle:oauth-token`.

### 6.2 Backup and key recovery
- `config/backup.php` enables archive verification and keeps archive encryption controlled by `BACKUP_ARCHIVE_PASSWORD`.
- `.env.example` documents the required secret without storing a value; production must use a secret manager.
- `routes/console.php` schedules `backup:clean`, `backup:run`, and `backup:monitor` after security and audit pruning.
- Native Windows execution is not treated as production proof; run `backup:run` and the restore drill on the Linux deployment/CI environment described in `docs/15-backup-and-disaster-recovery.md`.
- Verify schedule registration: `php artisan schedule:list`; verify command availability: `php artisan backup:run --help`.

### 6.3 Health checks and metrics
- Paths: `routes/health.php`, `app/Http/Controllers/HealthController.php`, `tests/Feature/HealthCheckTest.php`.
- Expose dependency health and counters for authorization failures, token issuance, latency, and key status without user data or tokens.
- Verify: `php artisan test tests/Feature/HealthCheckTest.php --compact`.

### 6.4 Audit retention and export
- Read-only CSV export is implemented at `admin.audit.export`, protected by the separate `audit.export` permission.
- Export accepts the bounded security-event filters, caps output at 1,000 rows, and includes only event, risk, actor, subject, timestamp, and redacted safe metadata. Database-audit export remains intentionally deferred until its provider query contract is finalized.
- Coverage: `tests/Feature/Admin/AuditExportTest.php` and `tests/Feature/Admin/AuditLogTest.php` (5 tests, 20 assertions).
- Remaining: retention command/runbook and destination-failure handling for scheduled exports.

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
