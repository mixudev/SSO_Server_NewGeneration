# Authorization & Role-Permission Architecture — Mixu SSO

- Document ID: `SSO-AUTHZ-001`
- Version: `0.3`
- Status: Baseline
- Date: `2026-09-17`

## 1. Purpose

Authentication dan authorization adalah concern berbeda.

`mixudev/laravel-authentication` menjadi authentication boundary untuk user authentication. Mixu SSO tetap bertanggung jawab menentukan siapa yang boleh mengakses dan memutasi **control plane / admin dashboard**.

Untuk authorization internal gunakan **Spatie Laravel Permission** sebagai package RBAC yang teruji, lalu gunakan Laravel Gate/Policy sebagai enforcement layer. Jangan membuat custom role/permission persistence sendiri.

Official compatibility saat ini: Spatie Laravel Permission v8 mendukung Laravel 12/13 dengan PHP 8.3+. Current stable yang tercatat di Packagist adalah `8.3.0`. citeturn722739search1turn722739search0

## 2. Decision

Gunakan:

```text
mixudev/laravel-authentication
        ↓
Authentication
        ↓
Spatie Laravel Permission
        ↓
Laravel Gate / Policy
        ↓
Admin use case
```

Install baseline:

```bash
composer require spatie/laravel-permission:^8.3
```

Kemudian publish migration/config sesuai dokumentasi resmi package dan jalankan migration. citeturn722739search5

`User` menggunakan `Spatie\Permission\Traits\HasRoles` dan tetap memenuhi contract Laravel `Authorizable`. Package mendaftarkan permissions ke Laravel Gate sehingga `can()` dan Blade `@can` dapat dipakai. citeturn722739search0turn722739search4

## 3. Why not custom RBAC?

Do not create project-owned tables seperti:

```text
roles
permissions
role_permissions
user_roles
```

untuk menggantikan schema package.

Project tetap memiliki **authorization domain semantics**, policy, scope, admin workflow, dan audit, tetapi persistence RBAC didelegasikan ke Spatie.

Ini mengurangi custom security surface dan memanfaatkan implementation yang sudah banyak digunakan serta memiliki compatibility/testing matrix untuk Laravel 13. citeturn722739search11

## 4. Initial access model

Release awal hanya memerlukan satu role:

```text
platform_admin
```

Dashboard access:

```text
Authenticated user
      ↓
Active platform_admin?
      ↓
Permission check
      ↓
Policy/resource check
      ↓
Admin action
```

Authentication saja TIDAK memberikan akses dashboard.

Jangan gunakan:

```php
$user->is_admin
```

atau:

```php
$user->email === 'admin@example.com'
```

sebagai security boundary.

## 5. Role vs permission

Gunakan prinsip Spatie:

- Role adalah group/bundle permission.
- Permission adalah capability spesifik.
- Permission umumnya diberikan ke role.
- User umumnya menerima capability melalui role, bukan direct permission.

Contoh:

```text
platform_admin
 ├── admin.dashboard.view
 ├── applications.view
 ├── applications.create
 ├── applications.update
 ├── applications.delete
 ├── applications.credentials.rotate
 ├── users.view
 ├── users.manage
 ├── sessions.view
 ├── sessions.revoke
 ├── audit.view
 └── security.manage
```

Ini mengikuti best practice package: roles mengelompokkan permissions dan granular permission keys dipakai untuk authorization checks. citeturn722739search12

## 6. Internal admin authorization vs external SSO authorization

Keduanya harus dipisahkan.

### Internal control plane

```text
Spatie Role/Permission
        ↓
Admin Dashboard
```

### External applications

```text
OIDC/OAuth2 scopes
       +
claim policies
       +
application policy
```

Jangan otomatis mengubah `platform_admin` menjadi OIDC scope.

Role internal SSO server tidak boleh bocor ke client application kecuali ada explicit claim policy dan permission yang dirancang untuk itu.

## 7. Permission naming

Gunakan stable machine keys:

```text
admin.dashboard.view
applications.view
applications.create
applications.update
applications.delete
applications.credentials.rotate
applications.redirect_uris.manage
applications.scopes.manage
applications.claims.manage
users.view
users.manage
sessions.view
sessions.revoke
organizations.view
organizations.manage
keys.view
keys.rotate
audit.view
audit.export
security.view
security.manage
settings.view
settings.manage
```

Jangan menggunakan label UI sebagai permission key.

## 8. Policy boundary

Permission check saja belum cukup.

Untuk resource scoped, wajib:

```text
permission
AND
resource ownership / organization scope
AND
resource status
AND
operation-specific invariant
```

Contoh: `applications.update` tidak berarti user boleh mengubah application milik organization lain.

## 9. High-risk authorization

Operasi berikut memerlukan permission khusus dan dapat mewajibkan recent authentication / step-up:

```text
client secret rotation
signing key rotation
redirect URI mutation
claim policy mutation
SAML certificate change
bulk session revocation
administrator role changes
backup policy changes
restore operations
security policy changes
```

## 10. Last-admin invariant

Sistem harus mempertahankan minimal satu administrator yang masih dapat mengelola platform.

Race condition harus diuji ketika dua admin mencoba menghapus/menurunkan privilege secara bersamaan.

## 11. Audit

Audit mandatory untuk:

```text
role assignment
role removal
permission mutation
privileged denial
admin login
high-risk admin action
application ownership mutation
backup/restore action
security setting change
```

Audit entry minimal membawa actor, action, target, outcome, timestamp, request/correlation id, dan safe metadata. Jangan masukkan token, password, private key, atau secret.

## 12. Testing invariants

Minimal security cases:

```text
unauthenticated → denied
authenticated ordinary user → denied
revoked admin → denied
role escalation → denied
self privilege assignment → denied
permission bypass via direct URL → denied
permission bypass via API → denied
cross-organization access → denied
mass assignment of role → denied
last-admin concurrent removal → invariant preserved
```

## 13. AI agent rule

AI agent wajib menggunakan package Spatie, bukan membuat replacement authorization system.

Sebelum menambah role/permission logic:

1. cari existing permission;
2. cari existing policy/gate;
3. gunakan Spatie APIs dan Laravel Gate/Policy;
4. jangan hardcode authorization pada view saja;
5. selalu enforce server-side;
6. tambahkan adversarial tests;
7. audit high-risk changes.
