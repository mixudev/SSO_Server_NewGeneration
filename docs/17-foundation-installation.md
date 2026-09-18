# Foundation Installation Baseline — Mixu SSO

- Document ID: `SSO-INSTALL-001`
- Version: `0.1`
- Status: Baseline
- Date: `2026-09-17`

## 1. Goal

Greenfield setup must install only approved foundation packages first.

## 2. Composer baseline

```bash
composer create-project laravel/laravel:^13.0 mixu-sso
cd mixu-sso

composer require mixudev/laravel-authentication
composer require laravel/passport
composer require spatie/laravel-permission:^8.3
composer require spatie/laravel-backup:^10
composer require mixudev/security-defense:^1.9
composer require mixudev/laravel-timezone:^1.0
composer require laravel/boost --dev
```

Exact version selection must be resolved by Composer against the actual PHP/runtime environment; do not force an incompatible lockfile.

## 3. Package roles

```text
mixudev/laravel-authentication
    = authentication

laravel/passport
    = OAuth2 infrastructure

spatie/laravel-permission
    = internal control-plane RBAC

spatie/laravel-backup
    = application/database backup

laravel/boost
    = AI/Laravel development tooling
```

## 4. Spatie Permission setup

Consult the official v8 prerequisites first, especially User model constraints. Then publish migration/config, add `HasRoles`, migrate, and seed the initial `platform_admin` role plus required permission keys. citeturn722739search0turn722739search5

Do not create duplicate `roles`/`permissions` relations/fields on the User model that conflict with Spatie's `HasRoles` implementation. citeturn722739search0

## 5. Spatie Backup setup

```bash
composer require spatie/laravel-backup:^10
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider" --tag=backup-config
```

Then configure:

```text
backup destination disks
archive encryption
retention/cleanup
monitoring
schedule
restore runbook
```

Production should use Linux and external backup storage. The current package supports multiple filesystems and health monitoring. citeturn230300search3turn230300search0

## 6. Windows development note

Do not use Windows production as the reference environment for Spatie Backup v10. For local development, actual backup integration tests must execute on Linux/WSL/CI where required server tooling is available. citeturn230300search4

## 7. Dashboard UI shell

Dashboard baseline remains:

```text
Blade components
+ Tailwind CSS v4
+ Bootstrap Icons (npm, self-hosted)
+ Alpine.js
+ Vite
```

Bundle dashboard CSS, JavaScript, fonts, and icons through the frontend pipeline. Keep business logic out of views and do not load runtime CDN assets.

## 8. Baseline verification

After foundation setup:

```bash
php artisan migrate
php artisan test
php artisan route:list
npm install
npm run build
php artisan backup:list
```

Development command policy:

- Windows: `composer run dev` starts Laravel, queue worker, and Vite. It intentionally does not start Pail because Pail requires the `pcntl` extension, which is unavailable in standard Windows PHP.
- Linux/WSL: `composer run dev:linux` starts Laravel, queue worker, Vite, and Pail.
- Manual logs: `composer run logs` on an environment where `pcntl` is available.

If `backup:list` cannot be used because the development OS is unsupported, record that limitation and verify backup behavior in Linux CI/staging.
