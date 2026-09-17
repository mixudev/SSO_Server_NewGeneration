# Mixu SSO / Identity Platform — Documentation

Dokumentasi ini adalah **single source of truth** untuk perancangan, pengembangan, pengujian, dan pemeliharaan sistem SSO/Identity Provider berbasis Laravel.

> Status: Architecture Baseline v0.4
> Tanggal: 2026-09-17
> Target framework: Laravel 13.x
> Core authentication: `mixudev/laravel-authentication`

## Struktur dokumentasi

| File | Tujuan |
|---|---|
| `01-prd.md` | Product Requirement Document: tujuan produk, pengguna, fitur, UX, roadmap, acceptance criteria. |
| `02-srs.md` | Software Requirements Specification: requirement fungsional/non-fungsional, protocol, data, API, security, testing. |
| `03-architecture.md` | Arsitektur target, bounded context, layer, dependency rule, flow, data model, extension points. |
| `04-protocol-contracts.md` | Kontrak OIDC, OAuth 2.0, SAML, discovery, claims, keys, client registration. |
| `05-data-model.md` | Entitas, tabel, relasi, lifecycle, indexing, retention, versioning. |
| `06-security-model.md` | Threat model, security invariants, key rotation, replay protection, SSRF/open redirect defense, audit. |
| `07-development-roadmap.md` | Tahapan implementasi dari foundation sampai SAML/multi-tenant/advanced features. |
| `08-ai-agent-contract.md` | Kontrak kerja AI agent agar kode dipecah per responsibility, tidak membuat file raksasa, dan tidak melanggar arsitektur. |
| `09-decision-log.md` | Architecture Decision Record (ADR); semua perubahan arsitektur besar harus dicatat. |
| `10-dashboard-ui.md` | Baseline dashboard: Tabler + Blade + Bootstrap 5, instalasi, struktur view, UI contract, deployment. |
| `11-routing-architecture.md` | Struktur route modular, ownership, middleware matrix, naming, protocol route boundaries. |
| `12-security-testing-strategy.md` | Strategi unit/feature/integration/protocol/adversarial/concurrency/property testing untuk mencoba menjebol invariant keamanan. |
| `13-development-workflow.md` | Workflow implementasi, slicing, definition of done, migrasi, dan sinkronisasi dokumentasi. |
| `14-authorization-and-permissions.md` | Spatie Laravel Permission sebagai RBAC untuk control plane, role/permission boundary, policies, scope, audit, dan privilege invariants. |
| `15-backup-and-disaster-recovery.md` | Spatie Laravel Backup, storage, encryption, retention, monitoring, restore drill, RPO/RTO, dan disaster recovery. |
| `16-security-threat-catalog.md` | Threat catalog dari auth, OIDC/SAML, injection, SSRF, XSS, CSRF, RCE paths, supply chain, DoS, backup, deployment, hingga insider threat. |

## Urutan membaca AI agent

1. `01-prd.md`
2. `02-srs.md`
3. `03-architecture.md`
4. `04-protocol-contracts.md`
5. `05-data-model.md`
6. `06-security-model.md`
7. `16-security-threat-catalog.md`
8. `14-authorization-and-permissions.md`
9. `15-backup-and-disaster-recovery.md`
10. `17-foundation-installation.md`
11. `08-ai-agent-contract.md`
12. `09-decision-log.md`
13. `10-dashboard-ui.md`
14. `11-routing-architecture.md`
15. `12-security-testing-strategy.md`
16. `13-development-workflow.md`
17. `07-development-roadmap.md`

## Aturan utama

- Authentication user **bukan** tanggung jawab SSO core. Gunakan `mixudev/laravel-authentication` sebagai adapter/authentication boundary.
- Authorization dashboard **tetap menjadi tanggung jawab Mixu SSO**. Authentication package tidak dianggap sebagai permission/RBAC system. Gunakan **Spatie Laravel Permission** untuk persistence/role-permission registry dan Laravel Gates/Policies sebagai enforcement layer.
- Release awal hanya membutuhkan role `platform_admin`, tetapi permission keys dan role-assignment boundary disiapkan sejak awal agar granular authorization dapat berkembang tanpa membongkar controller/domain.
- Jangan memakai hardcoded email, `is_admin` boolean, atau role-string checks tersebar sebagai security boundary.
- OAuth 2.0 token infrastructure boleh menggunakan Laravel Passport.
- OIDC Provider adalah layer di atas OAuth 2.0; Passport bukan dianggap sebagai implementasi OIDC lengkap.
- SAML selalu berada di balik protocol adapter sehingga domain core tidak bergantung pada library SAML tertentu.
- UI dashboard, domain logic, protocol handler, infrastructure, dan integration code harus dipisahkan.
- Dashboard admin memakai **Tabler official + Blade + Bootstrap 5**; Vue bukan fondasi dashboard.
- Tabler dipasang sebagai asset dependency melalui Vite dan dibuild menjadi asset statis untuk deployment shared hosting.
- `routes/web.php` adalah composition point; route kompleks dipecah per bounded context/protocol.
- Security test harus bersifat adversarial: test suite wajib mencoba replay, race condition, tenant breakout, redirect abuse, token tampering, parser abuse, SSRF, dan privilege escalation bila relevan.
- Jangan membuat controller/service raksasa. Satu class harus memiliki satu tanggung jawab utama.
- Semua flow security-critical wajib mempunyai test positif, negatif, abuse case, dan replay/expiry case.
- Endpoint discovery/metadata harus selalu mencerminkan konfigurasi aktif dan key material aktif.
- Breaking change harus dicatat dalam `09-decision-log.md` sebelum implementasi.

## Official dependency baseline

- `spatie/laravel-permission:^8.3` untuk internal RBAC/control-plane authorization. Spatie v8 mendukung Laravel 12/13 dan PHP 8.3+. citeturn722739search1turn722739search0
- `spatie/laravel-backup:^10` untuk application/database backup. Current Packagist release `10.3.3`; production baseline harus Linux, dan exact PHP/runtime requirements wajib diverifikasi saat installation. citeturn230300search6turn230300search4

Backup dan authorization merupakan bagian dari security architecture, bukan fitur tambahan.
