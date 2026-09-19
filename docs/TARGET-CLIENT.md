# Konfigurasi Single Sign-On (SSO)

## 1. Konfigurasi OIDC (OpenID Connect)

Gambar pertama menampilkan pengaturan ketika protokol **OIDC** dipilih.

### Pengaturan

- **SSO Protocol:** Memilih protokol **OIDC**.
- **Auth Mode:** Menentukan mode autentikasi. Saat ini diatur ke **Both**, yang mengizinkan pengguna login menggunakan kata sandi maupun SSO.
  - `Password only`
  - `OIDC only`
  - `Both`
- **Issuer URL:** Alamat URL penerbit token dari Identity Provider (IdP).

  Contoh:
  `https://auth.example.com/application/o/9router/`

- **Client ID & Client Secret:** Kredensial unik yang diberikan oleh Identity Provider (IdP) untuk mengidentifikasi dan mengautentikasi aplikasi.
- **Scopes:** Ruang lingkup informasi yang diminta dari IdP.

  Nilai:
  `openid profile email`

- **Login Button Label:** Teks yang ditampilkan pada tombol login SSO.

  Nilai:
  `Sign in with OIDC`

- **Redirect URI:** URL callback yang digunakan untuk mengembalikan pengguna ke aplikasi setelah proses autentikasi berhasil.

  Nilai:
  `http://localhost:8000/api/auth/oidc/callback`

### Tombol Aksi

- `Save OIDC settings` — Menyimpan konfigurasi OIDC.
- `Test connection` — Menguji koneksi dan konfigurasi ke Identity Provider.

---

## 2. Konfigurasi SAML 2.0

Gambar kedua menampilkan pengaturan ketika protokol **SAML 2.0** dipilih.

### Pengaturan

- **SSO Protocol:** Memilih protokol **SAML 2.0**.
- **Auth Mode:** Menentukan mode autentikasi yang digunakan.
  - `Password only`
  - `SAML only`
  - `Both`

- **Panduan & Impor:** Menyediakan:
  - **IdP Setup Guidelines & Provider Configuration Instructions** untuk membantu proses konfigurasi Identity Provider.
  - **1-Click IdP Metadata XML Import** untuk mengimpor metadata XML IdP dan mengisi konfigurasi secara otomatis.

- **Single Sign-On Service URL (`samlEntryPoint`):** URL endpoint SSO yang disediakan oleh Identity Provider (IdP).

- **SP Entity ID / Audience (`samlIssuer`):** Identitas unik untuk Service Provider (SP).

  Nilai:
  `urn:9router:sp`

- **IdP X.509 Certificate (`samlCert`):** Sertifikat publik dari Identity Provider dalam format PEM atau Base64. Sertifikat ini digunakan untuk memverifikasi tanda tangan digital pada SAML Assertion.

### Atribut Klaim

Bagian **Claims** digunakan untuk menentukan nama atribut yang dikirimkan oleh Identity Provider.

Contoh:

- **Email Claim Attribute:** `email`
- **Display Name Claim:** `name`

### Informasi Service Provider

Bagian ini menyediakan informasi yang diperlukan untuk konfigurasi Service Provider pada Identity Provider:

- **ACS Callback URL:** URL endpoint yang menerima SAML Response setelah proses autentikasi.
- **SP XML Metadata:** Metadata XML Service Provider yang dapat diunduh dan digunakan untuk konfigurasi pada IdP.

### Tombol Aksi

- `Save SAML settings` — Menyimpan konfigurasi SAML.
- `Test SAML settings` — Menguji konfigurasi dan koneksi SAML.