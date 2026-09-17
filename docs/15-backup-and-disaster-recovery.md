# Backup & Disaster Recovery — Mixu SSO

- Document ID: `SSO-BACKUP-001`
- Version: `0.1`
- Status: Baseline
- Date: `2026-09-17`

## 1. Objective

Backup Mixu SSO bukan sekadar membuat file ZIP. Karena SSO adalah identity/security control plane, backup harus mampu mendukung:

```text
backup
→ integrity verification
→ secure storage
→ retention
→ monitoring
→ restore drill
→ disaster recovery
```

Target utama:

- kehilangan database tidak menyebabkan identity registry hilang;
- application/client registry dapat dipulihkan;
- authorization configuration dapat dipulihkan;
- audit history dapat dipulihkan sesuai retention policy;
- key material dapat dipulihkan melalui prosedur terpisah yang aman;
- restore tidak memperkenalkan kembali secret yang sudah dicabut tanpa prosedur eksplisit.

## 2. Package decision

Gunakan official Spatie package:

```bash
composer require spatie/laravel-backup
```

Current Packagist release: `10.3.3`, compatible with Laravel 12/13 and PHP `^8.3`; package requires ZIP and external database dump tooling such as `mysqldump`/`pg_dump` depending on DB. Spatie's current documentation also states that the v10 package is not compatible with Windows servers. citeturn230300search6turn230300search4

**Production baseline:** Linux server. Use PHP 8.4+ as the conservative baseline and verify the exact package requirements during install because the live docs and Packagist metadata have slightly different PHP floor wording. citeturn230300search4turn230300search6

Untuk development di Windows, jangan menganggap actual backup execution bekerja identik. Gunakan Linux/WSL/CI atau lakukan backup integration testing pada Linux environment.

## 3. Installation

```bash
composer require spatie/laravel-backup

php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider" --tag=backup-config

php artisan backup:run
php artisan backup:list
```

Spatie menyediakan `backup:run`, `backup:clean`, `backup:list`, dan `backup:monitor`. citeturn230300search2turn230300search3

## 4. What is backed up

Spatie Backup membuat archive berisi file yang dipilih dan dump database yang dikonfigurasi. Backup dapat dikirim ke beberapa filesystem sekaligus. citeturn230300search1

### Include

- application source/config yang memang diperlukan untuk recovery;
- database;
- storage data yang menjadi system-of-record;
- selected runtime configuration yang tidak mengandung secret langsung.

### Exclude / handle separately

- `.env` secrets jika policy organisasi memisahkan secret lifecycle;
- private signing keys dari archive umum bila key custody dikelola terpisah;
- temporary files;
- cache;
- logs jika mengandung data sensitif berlebihan;
- node_modules/vendor jika dapat direbuild dan tidak dibutuhkan untuk forensic restore.

**Catatan:** keputusan include/exclude harus mengikuti recovery runbook, bukan asumsi.

## 5. Backup secret and key custody

Backup berisi data identity sangat sensitif.

Archive encryption harus aktif bila tersedia dan password/encryption key tidak boleh disimpan di dalam archive atau repository.

Spatie Backup v10 menyediakan archive password dan encryption, termasuk AES-256 ketika `default` memilih AES-256 yang tersedia. citeturn230300search5

Prinsip:

```text
Backup archive
      ↓ encrypted
Remote storage

Encryption secret
      ↓ separate trust boundary
Secret store / protected environment
```

Private signing keys memiliki kebijakan custody sendiri. Jangan menganggap application backup otomatis menjadi satu-satunya key escrow.

## 6. Storage strategy

Jangan bergantung pada local disk saja.

Spatie sendiri memperingatkan bahwa menyimpan backup hanya pada local filesystem bukan strategy yang cukup; mereka merekomendasikan external storage seperti S3/Dropbox dan mendukung multiple destination disks. citeturn230300search3

Baseline production:

```text
Primary database/server
        ↓
Encrypted backup
   ┌────┴────┐
   ↓         ↓
Remote A   Remote B
```

Prefer storage yang berbeda failure domain dari application server.

## 7. 3-2-1 principle

Target operational policy:

```text
3 copies
2 different storage/failure domains
1 copy off-site
```

Encrypted immutable/offline copy sangat disarankan untuk menghadapi ransomware atau destructive administrator compromise.

## 8. Retention

Retention harus sesuai risk dan storage budget.

Contoh baseline:

```text
hourly/daily short-term
weekly medium-term
monthly long-term
```

Jangan menghapus semua backup lama hanya karena disk hampir penuh.

Gunakan `backup:clean` dengan retention policy yang terdokumentasi. Spatie menyediakan cleanup untuk mencegah backup memenuhi storage. citeturn230300search1

## 9. Scheduling

Gunakan Laravel scheduler / cron.

Contoh:

```php
Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('01:30');
```

Sesuaikan dengan environment. citeturn450759search2

## 10. Backup monitoring

Backup yang tidak pernah diuji dianggap **untrusted** sampai dibuktikan dapat dipulihkan.

Gunakan:

```bash
php artisan backup:monitor
php artisan backup:list
```

Spatie dapat menilai backup unhealthy bila terlalu lama atau storage tidak mencukupi, dan menyediakan event `UnHealthyBackupWasFound`. Mereka bahkan merekomendasikan monitor di installation terpisah/server terpisah untuk menghindari kegagalan aplikasi utama membuat monitoring ikut mati. citeturn230300search0turn230300search2

## 11. Integrity verification

Untuk critical production backup:

- enable archive verification;
- verify remote object exists;
- verify expected size is within policy;
- periodically restore to isolated environment;
- compare schema/application health after restore.

Backup success ≠ restore success.

## 12. Restore policy

Restore tidak boleh menjadi `extract zip` saja.

Runbook:

```text
Isolate target
→ install known-good application version
→ provision dependencies
→ restore database
→ restore approved files
→ restore required key material under key-custody procedure
→ rotate compromised secrets if incident related
→ clear/rebuild caches
→ run migrations only when recovery plan says so
→ run health checks
→ run security verification
→ re-enable traffic
```

## 13. RPO / RTO

Project harus menetapkan angka resmi sebelum production.

Dokumentasikan:

```text
RPO = maksimum data loss yang dapat diterima
RTO = maksimum waktu recovery yang dapat diterima
```

Do not invent RPO/RTO in implementation code. They are operational policy decisions.

## 14. Backup security threats

Protect against:

```text
backup deletion
backup encryption/ransomware
backup credential theft
archive plaintext leakage
stolen storage credentials
restore-to-insecure-version
malicious backup injection
key/secret co-location
backup monitoring failure
silent backup corruption
```

## 15. Restore tests

At least periodically:

```text
fresh server restore
full DB restore
partial data restore where supported
key rotation after restore
revoked-secret verification
admin access verification
OIDC discovery/JWKS verification
session invalidation verification
backup archive decryption test
```

Test restore in an isolated environment; never overwrite production for a routine restore drill.

## 16. Incident response relationship

During suspected compromise:

1. preserve evidence;
2. do not delete attacker-accessible logs/backups blindly;
3. revoke compromised credentials;
4. assess key compromise;
5. preserve known-good backup points;
6. rebuild from trusted artifact;
7. rotate secrets/keys according to incident plan;
8. restore only from verified backups;
9. replay security events as needed for investigation.

## 17. AI agent rule

AI agent MAY configure Spatie Backup but MUST NOT silently change retention, destination, encryption, key custody, or restore semantics.

Any change affecting recovery guarantees requires documentation + tests + ADR when architectural.
