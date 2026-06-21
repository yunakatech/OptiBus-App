# Coolify Deploy Guide

Panduan ini untuk deploy project Laravel 13 + Inertia/Svelte ini ke Coolify dengan build pack `Dockerfile`.

Repo ini sudah punya `Dockerfile` production dan `docker/start.sh`, jadi kita tidak perlu bikin konfigurasi Docker Compose khusus untuk aplikasi web utamanya.

## Ringkasan Arsitektur

- Web app dibangun dari `Dockerfile` di root repo.
- Container menjalankan `php artisan serve` lewat `docker/start.sh`.
- `docker/start.sh` akan cek `APP_KEY`, siapkan folder storage, cache config/routes/view, jalankan migrasi jika `RUN_MIGRATIONS=true`, lalu start server di `PORT=10000`.
- Queue default project ini memakai database.
- Scheduler Laravel ada di `routes/console.php`, jadi perlu task cron `schedule:run`.

## Prasyarat

- Repository sudah ada di GitHub/GitLab/Bitbucket yang bisa diakses Coolify.
- Server Coolify sudah terhubung dan siap buat aplikasi baru.
- Domain atau subdomain sudah diarahkan ke Coolify.
- Database PostgreSQL sudah tersedia, baik dari Coolify maupun dari provider eksternal.

## 1. Buat Application di Coolify

1. Buka Coolify.
2. Pilih `New Resource` -> `Application`.
3. Pilih source repository project ini.
4. Pada build pack, pilih `Dockerfile`.
5. Pastikan path `Dockerfile` mengarah ke file root repo.
6. Build context tetap di root project.
7. Set port aplikasi ke `10000`.
8. Health check path isi `/up`.
9. Tambahkan domain yang akan dipakai untuk production.
10. Simpan lalu deploy.

Kalau Coolify meminta custom start command, biarkan kosong. Entry point sudah diatur di `docker/start.sh`.

## 2. Environment Variables

Coolify memisahkan build-time dan runtime variables. Untuk aplikasi ini, mayoritas cukup runtime variable. Kalau ingin judul aplikasi di frontend ikut berubah, tambahkan juga build variable `VITE_APP_NAME`.

### Runtime variables

```env
APP_NAME=OptiBus
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=https://domain-anda.com
APP_TIMEZONE=Asia/Makassar
FORCE_HTTPS=true
TRUSTED_PROXIES=*
ASSET_URL=

LOG_CHANNEL=stack
LOG_STACK=stderr
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_URL=postgresql://USER:PASSWORD@HOST:5432/DB_NAME?sslmode=require&channel_binding=require
DB_SSLMODE=require

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
CACHE_STORE=file

RUN_MIGRATIONS=true
```

### Build variable opsional

```env
VITE_APP_NAME=OptiBus
```

Catatan:

- Kalau pakai Neon, cukup isi `DB_URL` dari connection string Neon.
- Jangan isi `APP_URL` dengan `http://localhost`, `http://0.0.0.0`, atau host internal container.
- `ASSET_URL` biarkan kosong kecuali kamu memang memakai CDN atau domain asset terpisah.
- `APP_KEY` wajib diisi sebelum container start, karena `docker/start.sh` akan berhenti kalau nilainya kosong.
- Kalau kamu belum siap migrasi otomatis saat setiap deploy, ubah `RUN_MIGRATIONS=false` setelah deploy awal sukses.

## 3. Persistent Storage

Project ini memakai storage lokal untuk beberapa kebutuhan runtime, jadi sebaiknya mount persistent volume di:

- `/var/www/html/storage`

Ini akan membantu kalau ada file upload, log, atau file sementara yang perlu bertahan antar deploy.

`public/storage` akan dibuat otomatis oleh entrypoint lewat `php artisan storage:link`.

## 4. Database

Kalau pakai PostgreSQL dari Coolify:

1. Buat resource `Database` -> `PostgreSQL`.
2. Ambil credential yang diberikan Coolify.
3. Isikan ke environment variable aplikasi.
4. Pastikan database itu bisa diakses dari service aplikasi.

Kalau pakai database eksternal:

1. Pastikan jaringan dan firewall mengizinkan koneksi dari server Coolify.
2. Gunakan credential production yang aman.

Project ini memakai `SESSION_DRIVER=database` dan `QUEUE_CONNECTION=database`, jadi tabel session dan jobs harus tersedia. Jalankan migrasi saat deploy awal.

## 5. Queue Worker

Karena queue default memakai database, web service sebaiknya tidak menangani job queue sendiri.

Buat resource terpisah untuk worker, lalu jalankan command seperti ini:

```bash
php artisan queue:work --sleep=3 --tries=1 --timeout=0
```

Tips:

- Jangan gunakan `queue:listen` untuk production kalau tidak perlu.
- Worker tidak butuh domain publik.
- Pastikan worker pakai environment yang sama dengan web app.

## 6. Scheduler Laravel

Di `routes/console.php` ada task terjadwal harian, jadi Coolify perlu cron yang memanggil scheduler tiap menit.

Tambahkan scheduled command:

```bash
php artisan schedule:run
```

Dengan cron syntax:

```cron
* * * * *
```

Coolify mendukung cron syntax standar, jadi `* * * * *` atau preset setara `every_minute` sama-sama cocok.

## 7. Langkah Deploy Awal

1. Isi `APP_KEY`.
2. Set konfigurasi database.
3. Aktifkan `RUN_MIGRATIONS=true`.
4. Deploy application web.
5. Deploy worker queue.
6. Aktifkan scheduler per menit.
7. Cek log deploy pertama.

Kalau deploy pertama berhasil, lanjut cek halaman:

- `/up`
- `/login`
- halaman utama aplikasi

## 8. Checklist Setelah Deploy

- Asset frontend berhasil dibuild.
- `APP_URL` sudah sesuai domain production.
- HTTPS aktif di domain Coolify.
- Worker queue berjalan.
- Scheduler jalan tiap menit.
- Storage persistent sudah terpasang kalau memang dibutuhkan.

## 9. Troubleshooting Cepat

- Kalau muncul error `APP_KEY belum diisi`, isi `APP_KEY` dulu lalu redeploy.
- Kalau health check gagal, pastikan port aplikasi benar-benar `10000`.
- Kalau halaman loading tapi asset tidak muncul, cek ulang build log dan pastikan build pack yang dipakai adalah `Dockerfile`.
- Kalau `woff2`, CSS, atau JS timeout di browser, buka `View Source` atau Network tab dan cek host asset-nya. Jika masih `localhost` atau host internal, perbaiki `APP_URL` dan kosongkan `ASSET_URL`, lalu redeploy.
- Kalau job queue menumpuk, cek apakah worker terpisah benar-benar aktif.
- Kalau task harian tidak jalan, cek scheduled command `schedule:run`.

## Referensi Coolify

- Dockerfile build pack: https://coolify.io/docs/applications/build-packs/dockerfile
- Environment variables: https://coolify.io/docs/knowledge-base/environment-variables
- Persistent storage: https://coolify.io/docs/knowledge-base/persistent-storage
- Cron syntax: https://coolify.io/docs/knowledge-base/cron-syntax
