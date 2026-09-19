# Provider Usage Guide

## 1. Provider URL dan discovery

Production provider wajib memakai HTTPS dan issuer yang stabil.

```text
ISSUER=https://sso.example.com
DISCOVERY=https://sso.example.com/.well-known/openid-configuration
JWKS=https://sso.example.com/.well-known/jwks.json
AUTHORIZATION=https://sso.example.com/oauth/authorize
TOKEN=https://sso.example.com/oauth/token
USERINFO=https://sso.example.com/oauth/userinfo
END_SESSION=https://sso.example.com/oauth/end-session
REVOCATION=https://sso.example.com/oauth/revoke
```

Client harus mengambil discovery dari issuer, bukan membentuk endpoint berdasarkan asumsi.

## 2. Registrasi application

Admin membuat application melalui dashboard:

1. Buat organization aktif.
2. Buat application melalui wizard.
3. Pilih protocol `oauth2` atau `oidc`.
4. Pilih `confidential_web` atau `public_spa`.
5. Daftarkan login redirect URI secara exact.
6. Daftarkan post-logout redirect URI secara exact bila logout redirect digunakan.
7. Pilih scope aktif yang memang diperlukan.
8. Pilih claim policy untuk OIDC.
9. Selesaikan security/session policy.
10. Review dan create.
11. Issue credential dari detail application.
12. Simpan secret hanya pada tampilan issue/rotate satu kali.
13. Activate application setelah konfigurasi dan credential siap.

Jangan menggunakan wildcard redirect URI, fragment, userinfo, atau URI yang tidak terdaftar.

## 3. Confidential web client

Use case: aplikasi server-side yang dapat menyimpan secret.

Request authorization minimal:

```text
GET /oauth/authorize?
  client_id={CLIENT_ID}&
  redirect_uri={EXACT_REGISTERED_URI}&
  response_type=code&
  scope=openid%20profile%20email&
  state={HIGH_ENTROPY_STATE}&
  code_challenge={BASE64URL_SHA256_VERIFIER}&
  code_challenge_method=S256&
  nonce={HIGH_ENTROPY_NONCE}
```

Authorization code ditukar di backend client:

```text
POST /oauth/token
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code&
client_id={CLIENT_ID}&
client_secret={CLIENT_SECRET}&
redirect_uri={EXACT_REGISTERED_URI}&
code={ONE_TIME_CODE}&
code_verifier={ORIGINAL_VERIFIER}
```

Jangan menukar code dari browser langsung dengan secret confidential.

## 4. Public SPA client

Use case: browser application yang tidak dapat menyimpan secret.

- Tidak memakai client secret.
- PKCE `S256` wajib.
- `state` wajib.
- Untuk OIDC, `nonce` wajib.
- Token handling harus mengikuti storage policy yang ketat; hindari localStorage untuk token jangka panjang.
- Redirect harus HTTPS pada production.
- Logout dan refresh-token policy harus ditinjau sebelum production exposure.

## 5. Token response

OAuth2 response dapat berisi:

```json
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "[REDACTED]",
  "refresh_token": "[REDACTED]",
  "scope": "openid profile email",
  "id_token": "[REDACTED]"
}
```

`id_token` hanya relevan untuk OIDC. Client harus memvalidasi signature, issuer, audience, expiry, nonce, dan claims sebelum membuat session lokal.

## 6. UserInfo

```text
GET /oauth/userinfo
Authorization: Bearer [ACCESS_TOKEN]
```

UserInfo bukan pengganti validasi ID Token. Claim yang dikembalikan bergantung pada access token scope dan application claim policy.

## 7. Logout

RP-Initiated Logout:

```text
GET /oauth/end-session?
  id_token_hint={OPTIONAL_ID_TOKEN}&
  client_id={CLIENT_ID}&
  post_logout_redirect_uri={REGISTERED_LOGOUT_URI}&
  state={OPAQUE_STATE}
```

Provider saat ini memerlukan authenticated browser session. `post_logout_redirect_uri` harus terdaftar sebagai redirect dengan kind `logout`. Jangan mempercayai URL redirect dari user tanpa exact registry validation.

Front-channel dan back-channel logout belum tersedia.

## 8. Revocation

```text
POST /oauth/revoke
Authorization: Bearer [ACCESS_TOKEN]
Content-Type: application/x-www-form-urlencoded

token=[TOKEN]
```

Provider memverifikasi bearer token melalui Passport sebelum memakai `jti`, kemudian mencabut access token dan refresh-token records yang berkaitan. Client tetap harus menghapus session/token lokal setelah response berhasil.

## 9. Admin lifecycle

| Operasi | Dampak |
|---|---|
| Suspend application | Authorization/token validity harus ditolak oleh runtime client enforcement. |
| Revoke credential | Credential lama tidak boleh dipakai lagi. |
| Rotate credential | Credential generation baru dibuat; secret lama tidak ditampilkan ulang. |
| Reactivate | Membuat generation/client baru; jangan menghidupkan secret/client lama. |
| Rotate signing key | JWKS mempertahankan key lama selama overlap terbatas. |

## 10. Direct login dan application portal

Jika user membuka provider secara langsung:

1. User login melalui route authentication provider `GET /login`.
2. Setelah login sukses, authentication package mengarahkan user ke `/portal`.
3. Portal hanya menampilkan application yang memiliki assignment aktif pada user.
4. Application harus aktif, organization harus aktif, dan credential harus aktif.
5. Jika tidak ada assignment, portal menampilkan empty state dan tidak membuka application apa pun.
6. Tombol `Open application` memakai `homepage_url` yang sudah terdaftar pada application; portal tidak menerima `next`, `return_url`, atau redirect URL dari browser.
7. Aplikasi client kemudian menyediakan halaman login miliknya sendiri dan memulai `route('ssoclient.redirect')`.

Portal tidak menggantikan OAuth authorization endpoint. Request ke `/oauth/authorize` yang membawa client terdaftar tetap melalui authorization transaction, consent, PKCE, dan redirect URI application tersebut. Portal juga tidak memberikan akses berdasarkan role platform secara otomatis; akses application harus diatur melalui `application_user_access` dengan permission admin `applications.access.view` dan `applications.access.manage`.

## 11. Operasi dan health

```text
GET /health/live
GET /health/ready
```

Readiness hanya mempublikasikan status dependency dan jumlah verification key. Private key, token, secret, dan database detail tidak boleh muncul.

Backup terjadwal:

- `security-defense:prune` 02:00
- `audit:prune` 02:15
- `backup:clean` 02:30
- `backup:run` 03:00
- `backup:monitor` 03:30

Backup restore drill Linux masih harus dijalankan sebelum production sign-off.
