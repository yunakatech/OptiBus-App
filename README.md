# OptiBus

OptiBus adalah aplikasi operasional travel berbasis Laravel, Inertia, dan
Svelte. Aplikasi menangani booking penumpang, keberangkatan, bagasi, carter,
jadwal, armada, pool, user, laporan, pembayaran, dan SaaS multi-tenant.

## Teknologi

- Laravel 13 dan PHP 8.3+
- Inertia.js 3, Svelte 5, TypeScript
- Vite 8, Tailwind CSS 4, shadcn-svelte/bits-ui
- PostgreSQL untuk production; SQLite untuk development/test sederhana
- Socialite untuk Google OAuth
- Supabase Storage opsional untuk foto profil
- Mayar opsional untuk pembayaran subscription SaaS

## Fitur

### Operasional

- Booking Console untuk booking dan monitoring keberangkatan.
- Booking beberapa kursi dengan data penumpang berbeda per kursi.
- Reschedule ke tanggal, jam, unit, dan kursi yang tersedia.
- Detail booking, tiket, manifest, pembatalan, dan pembayaran.
- Wizard jadwal berulang mingguan: waktu, kendaraan, dan segment.
- Jam keberangkatan bebas; segment dapat dicocokkan otomatis atau manual.
- Data carter dengan invoice, BOP, DP, dan status pembayaran.
- Data bagasi dengan tracking, pembayaran, filter kondisi, serta insiden rusak
  atau hilang.
- History insiden bagasi tetap tersimpan setelah klaim selesai atau ditolak.
- Bulk action status pembayaran sesuai aturan sumber data.

### Master data

- Rute reguler dan rute carter.
- Segment perjalanan dan jam pickup.
- Kategori armada, layout kursi, armada, driver, dan pool.
- Customer reguler, customer bagasi, dan customer carter.
- Jadwal, mapping kendaraan, dan mapping segment.

### Multi-tenant dan pool

- Satu aplikasi melayani banyak tenant.
- Tenant memiliki pool, user, role, permission, dan data operasional sendiri.
- `users.tenant_id` menjadi tenant utama user saat ini.
- User dapat memiliki beberapa pool dan mengatur default pool.
- Superadmin dapat mengelola platform dan berpindah tenant.
- `Semua Tenant` hanya mode baca lintas tenant. Create, update, dan delete
  wajib memakai tenant aktif.
- `Semua Pool` boleh untuk baca dalam satu tenant. Booking dan operasi yang
  membutuhkan pool harus memakai pool terpilih atau default pool.

### User dan SaaS

- Login email/password dan Google OAuth.
- Tenant Invitation untuk mengundang user Google ke tenant yang sudah ada.
- Role dan permission terpisah untuk platform, tenant, pool, booking, bagasi,
  pembayaran, laporan, dan master data.
- Onboarding tidak dijalankan untuk user baru yang memiliki invitation tenant.
- Foto profil dapat disimpan ke Supabase Storage; foto lama diganti saat upload.
- Platform Dashboard mengelola tenant, plan, subscription, invoice, dan
  payment gateway.

## Struktur Project

```text
app/                    Controller, service, middleware, dan support
database/migrations/    Struktur database dan perubahan schema
resources/js/            Komponen dan halaman Svelte
routes/web.php           Route web dan API session-auth
routes/settings.php      Route profile dan settings
docs/                    Panduan deployment dan integrasi
public/build/            Asset hasil build Vite
```

API admin saat ini didefinisikan di `routes/web.php` dengan prefix
`/api/admin`. Tidak ada file `routes/api.php` terpisah.

## Persyaratan

- PHP 8.3+
- Composer 2
- Node.js 22 dan npm
- PostgreSQL untuk konfigurasi yang mendekati production
- Chromium bila menggunakan fitur PDF tertentu

## Instalasi Lokal

```bash
git clone <repository-url> OptiBus-App
cd OptiBus-App
composer install
copy .env.example .env
php artisan key:generate
npm install
php artisan migrate
composer dev
```

Linux/macOS gunakan `cp .env.example .env` sebagai pengganti `copy`.

Alternatif menjalankan service terpisah:

```bash
php artisan serve
npm run dev
```

Halaman lokal utama:

- `http://localhost:8000/`
- `http://localhost:8000/login`
- `http://localhost:8000/dashboard`
- `http://localhost:8000/booking-console`
- `http://localhost:8000/bookings`

## Environment

Jangan commit `.env`, API key, password database, secret OAuth, atau secret
webhook. Untuk Vercel, isi variable melalui Project Settings > Environment
Variables. Gunakan `.env.example` dan `vercel.env.example` sebagai referensi.

### Aplikasi dan database

```env
APP_NAME=OptiBus
APP_ENV=local
APP_KEY=
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Makassar
FORCE_HTTPS=false

DB_CONNECTION=pgsql
DB_URL=postgresql://USER:PASSWORD@HOST:5432/DATABASE?sslmode=require
DB_SSLMODE=require
```

Untuk local SQLite, gunakan `DB_CONNECTION=sqlite`.

### Google OAuth

```env
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

Production harus memakai callback yang sama persis dengan Google Cloud Console,
misalnya `https://domain-anda.com/auth/google/callback`. Error
`redirect_uri_mismatch` berarti protocol, domain, port, path, atau slash tidak
identik.

### Email invitation

Mailer Laravel dipakai untuk mengirim invitation tenant:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME=OptiBus
```

Jika mailer belum aktif, invitation tetap tercatat dan dapat dikirim ulang.

### Supabase Storage avatar

Bucket default adalah `avatars`.

```env
SUPABASE_STORAGE_KEY=...
SUPABASE_STORAGE_SECRET=...
SUPABASE_STORAGE_REGION=us-east-1
SUPABASE_STORAGE_BUCKET=avatars
SUPABASE_STORAGE_ENDPOINT=https://PROJECT_REF.storage.supabase.co/storage/v1/s3
SUPABASE_STORAGE_URL=https://PROJECT_REF.supabase.co/storage/v1/object/public/avatars
```

`SUPABASE_STORAGE_KEY` dan `SUPABASE_STORAGE_SECRET` adalah credential
S3-compatible Supabase Storage, bukan Supabase anon key. Bucket harus dibuat
lebih dahulu.

### SaaS, Mayar, dan bantuan

```env
SAAS_FEATURE_GATING_ENABLED=true
SAAS_TRIAL_DAYS=14
SAAS_GRACE_PERIOD_DAYS=7
MAYAR_ENABLED=false
MAYAR_API_KEY=...
MAYAR_WEBHOOK_SECRET=...
VITE_SUPPORT_WHATSAPP_NUMBER=6287778110950
```

## Aturan Tenant dan Pool

Sebelum membuat atau mengubah data operasional:

1. Pilih tenant aktif, bukan `Semua Tenant`.
2. Pilih pool aktif bila operasi membutuhkan pool.
3. Pastikan user memiliki permission dan akses pool.
4. Untuk Booking Console, gunakan default pool atau pilih pool eksplisit.

Backend menolak write tanpa tenant aktif dengan HTTP `409`:

```text
Pilih tenant terlebih dahulu sebelum membuat atau mengubah data.
```

`Semua Tenant` hanya boleh untuk dashboard, laporan, list, dan monitoring
lintas tenant sesuai permission. Superadmin juga wajib memilih tenant untuk
write.

## Route Utama

### Publik dan auth

- `/`, `/pricing`, `/login`
- `/auth/google/redirect`, `/auth/google/callback`
- `/onboarding`

### Operasional

- `/dashboard`, `/booking-console`, `/bookings`, `/payments`
- `/charters`, `/luggages`, `/report`
- `/admin-ops/flows`

### Master data

- `/admin-ops/rute-induk`, `/admin-ops/jadwal`, `/admin-ops/segments`
- `/admin-ops/tarif-bagasi`, `/admin-ops/kategori-armada`
- `/admin-ops/armada`, `/admin-ops/driver`, `/admin-ops/pool`
- `/admin-ops/customers`

### Akses dan platform

- `/admin-ops/users`, `/admin-ops/roles`, `/admin-ops/logs`
- `/admin-ops/reports`, `/platform/dashboard`, `/admin-ops/saas`
- `/subscription`

Route private memakai session auth, verification, permission, subscription,
dan scope tenant/pool sesuai kebutuhan.

## Migrasi Production

```bash
php artisan migrate --force
php artisan migrate:status
```

Migration versi sekarang mencakup tenant SaaS, akses pool, default pool user,
tenant invitation, avatar user, serta insiden bagasi. Jangan mengubah migration
lama yang sudah dijalankan production. Buat migration baru untuk schema baru.

## Import Data Legacy

Atur koneksi legacy di `.env`, lalu dry-run:

```bash
php artisan legacy:import-booking-core --dry-run
```

Import booking core dan data operasi:

```bash
php artisan legacy:import-booking-core --truncate --chunk=1000
php artisan legacy:import-operations --truncate --chunk=1000
```

`--truncate` mengosongkan tabel target. Pastikan backup dan review dry-run.

## Queue dan Scheduler

Task terjadwal:

- Check subscription setiap hari pukul 01:00.
- Generate invoice subscription setiap hari pukul 02:00.
- Close manifest yang sudah lewat setiap 5 menit.

Server non-serverless perlu menjalankan scheduler tiap menit:

```bash
php artisan schedule:run
```

Worker database queue:

```bash
php artisan queue:work --sleep=3 --tries=1 --timeout=0
```

Vercel memakai konfigurasi cookie session dan queue sync. Database production
tetap wajib memiliki semua tabel hasil migration.

## Deployment

### Vercel

Vercel menjalankan Laravel melalui `api/index.php` dan `vercel.json`.

1. Isi environment variables di Vercel Project Settings.
2. Pastikan `APP_URL`, database, Google callback, mailer, dan storage benar.
3. Build dan deploy:

```bash
npm run build
vercel --prod
```

4. Jalankan migration pada environment yang memiliki akses database:

```bash
php artisan migrate --force
```

5. Hard refresh browser bila bundle lama masih tersimpan.

Gunakan [vercel.env.example](vercel.env.example) sebagai checklist variable.

### Docker atau Coolify

Project memiliki `Dockerfile` production dan `docker/start.sh`. Entry point
menyiapkan cache, storage link, dan migration bila `RUN_MIGRATIONS=true`.

```bash
docker build -t optibus .
docker run --env-file .env -p 10000:10000 optibus
```

Baca [docs/coolify-deploy.md](docs/coolify-deploy.md) untuk web, worker,
scheduler, database, dan persistent volume.

### Cloudflare

Laravel tidak dapat dijalankan langsung di Cloudflare Pages atau Workers tanpa
rewrite arsitektur. Jalur saat ini: Laravel tetap di origin hosting, Cloudflare
dipakai sebagai DNS, CDN, WAF, dan proxy. Baca
[docs/cloudflare-deploy.md](docs/cloudflare-deploy.md).

## Test dan Quality Check

```bash
npm run build
npm run types:check
php artisan test
composer ci:check
```

Test fokus:

```bash
php artisan test --filter=AdminOpsApi
php artisan test --filter=Booking
php artisan test --filter=PaymentPageTest
php artisan test --filter=LuggageIncident
php artisan test --filter=TenantContext
```

Jika type check melaporkan file Wayfinder generated, generate ulang:

```bash
node scripts/generate-wayfinder.mjs --with-form
npm run types:check
```

## Troubleshooting

### `redirect_uri_mismatch`

Samakan `GOOGLE_REDIRECT_URI` dengan Authorized redirect URI di Google Cloud
Console secara persis, termasuk protocol, domain, port, path, dan slash.

### `Tabel tenant invitations belum tersedia`

```bash
php artisan migrate --force
```

Jalankan pada database yang benar-benar dipakai aplikasi.

### Foto profil tidak berubah

- Pastikan kolom `users.avatar` sudah dimigrasikan.
- Pastikan bucket `avatars` tersedia.
- Pastikan variable Supabase Storage benar.
- Bersihkan cache dan redeploy:

```bash
php artisan optimize:clear
```

### Write ditolak saat `Semua Tenant`

Pilih tenant aktif. Ini perilaku read-only yang disengaja.

### Manifest sudah close

Edit, cancel, dan reschedule penumpang ditolak setelah manifest ditutup. History
dan laporan tetap dapat dilihat sesuai permission.

## Dokumentasi Tambahan

- [Cloudflare deployment](docs/cloudflare-deploy.md)
- [Coolify deployment](docs/coolify-deploy.md)
- [Render dan Neon deployment](docs/render-neon-deploy.md)
- [Mayar payment gateway](docs/mayar-payment-gateway.md)
- [Production performance](docs/production-performance.md)
- [Vercel environment baseline](vercel.env.example)

## Lisensi

OptiBus adalah product project internal. Lisensi dan hak distribusi mengikuti
ketentuan repository serta organisasi pemilik project.
