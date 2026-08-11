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
webhook. Untuk server production, isi variable langsung pada `.env` di server
Ubuntu dan batasi permission file tersebut. Gunakan `.env.example` sebagai
referensi.

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

Server Ubuntu perlu menjalankan scheduler tiap menit:

```bash
php artisan schedule:run
```

Worker database queue:

```bash
php artisan queue:work --sleep=3 --tries=1 --timeout=0
```

Database production wajib memiliki semua tabel hasil migration. Queue worker
dan scheduler sebaiknya dijalankan sebagai service terpisah agar tidak berhenti
saat proses web di-restart.

## Deployment

### Linux Ubuntu

Deployment utama OptiBus menggunakan Ubuntu 22.04/24.04 dengan Nginx,
PHP-FPM, PostgreSQL, Supervisor, dan Node.js untuk build asset. Pastikan domain
sudah mengarah ke IP server dan PHP 8.3+, Composer 2, Node.js 22, Nginx,
Supervisor, serta ekstensi PHP PostgreSQL sudah terpasang.

#### 1. Siapkan folder aplikasi

```bash
sudo mkdir -p /var/www
sudo git clone <repository-url> /var/www/optibus
sudo chown -R "$USER":www-data /var/www/optibus
cd /var/www/optibus
```

#### 2. Install dependency dan environment

```bash
composer install --no-dev --optimize-autoloader
npm ci
cp .env.example .env
php artisan key:generate
nano .env
```

Minimal production environment:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
FORCE_HTTPS=true
DB_CONNECTION=pgsql
DB_URL=postgresql://USER:PASSWORD@HOST:5432/DATABASE?sslmode=require
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

Isi juga Google OAuth, mailer, Supabase Storage, dan Mayar bila fitur tersebut
digunakan. Callback Google production harus memakai:
`https://domain-anda.com/auth/google/callback`.

#### 3. Migration, storage, dan asset

```bash
php artisan migrate --force
php artisan storage:link
npm run build
php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache public/build
sudo chmod -R ug+rwx storage bootstrap/cache
```

Jangan menjalankan `php artisan migrate:fresh` pada production.

#### 4. Konfigurasi Nginx

Buat `/etc/nginx/sites-available/optibus`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name domain-anda.com www.domain-anda.com;
    root /var/www/optibus/public;

    index index.php;
    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Aktifkan site dan uji konfigurasi:

```bash
sudo ln -s /etc/nginx/sites-available/optibus /etc/nginx/sites-enabled/optibus
sudo nginx -t
sudo systemctl reload nginx
```

Sesuaikan socket `php8.3-fpm.sock` bila server menggunakan versi PHP berbeda.

#### 5. Queue worker dengan Supervisor

Buat `/etc/supervisor/conf.d/optibus-worker.conf`:

```ini
[program:optibus-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/optibus/artisan queue:work --sleep=3 --tries=1 --timeout=0
directory=/var/www/optibus
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/optibus/storage/logs/worker.log
stopwaitsecs=3600
```

Aktifkan worker:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart optibus-worker:*
```

#### 6. Scheduler cron

Tambahkan cron untuk user `www-data`:

```bash
sudo crontab -u www-data -e
```

Isi:

```cron
* * * * * cd /var/www/optibus && php artisan schedule:run >> /dev/null 2>&1
```

#### 7. HTTPS dan deploy berikutnya

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d domain-anda.com -d www.domain-anda.com
```

Untuk update aplikasi berikutnya:

```bash
cd /var/www/optibus
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
sudo supervisorctl restart optibus-worker:*
sudo systemctl reload nginx
```

Simpan backup database sebelum migration production dan cek log dengan:
`storage/logs/laravel.log`, `journalctl -u nginx`, serta log Supervisor.

### Docker atau Coolify

Project memiliki `Dockerfile` production dan `docker/start.sh`. Entry point
menyiapkan cache, storage link, dan migration bila `RUN_MIGRATIONS=true`.

```bash
docker build -t optibus .
docker run --env-file .env -p 10000:10000 optibus
```

Baca [docs/coolify-deploy.md](docs/coolify-deploy.md) untuk web, worker,
scheduler, database, dan persistent volume.

### Cloudflare sebagai proxy

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

## Lisensi

OptiBus adalah product project internal. Lisensi dan hak distribusi mengikuti
ketentuan repository serta organisasi pemilik project.
