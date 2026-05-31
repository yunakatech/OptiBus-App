# Qbus Production Performance Notes

Panduan ini untuk deployment Laravel + Svelte/Inertia di Render, Neon PostgreSQL, dan Cloudflare tanpa hardcode domain aplikasi.

## Cloudflare DNS/CDN

- SSL/TLS mode: gunakan `Full (strict)` jika origin Render sudah HTTPS. Hindari `Flexible` karena sering memicu mixed content dan redirect loop.
- Edge Certificates: aktifkan `Always Use HTTPS`, `Automatic HTTPS Rewrites`, Brotli, HTTP/3, dan TLS 1.3.
- DNS: arahkan custom domain ke Render sesuai instruksi Render, lalu aktifkan proxy orange cloud setelah domain stabil.
- Cache Rule aman untuk Laravel/Inertia: cache hanya path static seperti `/build/*`, `/favicon.ico`, `/favicon.svg`, `/apple-touch-icon.png`, `/pwa-icon-*.png`, `/manifest.json`, dan `/style.css`.
- Jangan cache HTML, `/login`, `/dashboard`, `/bookings*`, `/admin-ops*`, `/api/*`, atau response yang membawa cookie/session.
- Browser TTL static asset: `1 year` untuk `/build/*` karena nama file Vite sudah hashed. Untuk `/manifest.json`, gunakan TTL pendek seperti `5 minutes` sampai PWA icon final.
- Jika memakai subdomain khusus asset, set `ASSET_URL=https://asset-domain.example.com`. Jika tidak, biarkan kosong dan cukup set `APP_URL=https://domain-app.example.com`.

## Render + Neon Environment

Gunakan pooled connection Neon untuk request web:

```env
APP_NAME=Qbus
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example.com
FORCE_HTTPS=true
TRUSTED_PROXIES=*

DB_CONNECTION=pgsql
DB_URL=postgresql://DB_USER:DB_PASSWORD@DB_POOLER_HOST/DB_NAME?sslmode=require&channel_binding=require
DB_SSLMODE=require
DB_SCHEMA=public
DB_PERSISTENT=false
DB_APPLICATION_NAME=qbus-render

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=file
QUEUE_CONNECTION=sync
RUN_MIGRATIONS=true
```

Catatan Neon: host pooled biasanya mengandung `-pooler`. Biarkan `DB_PERSISTENT=false` untuk Render free/single process kecuali sudah diuji dengan beban nyata. Migration Laravel tetap bisa lewat pooler untuk migration normal, tetapi hindari migration yang memakai `CREATE INDEX CONCURRENTLY` karena migration Laravel berjalan di transaksi.

## Laravel Cache Startup

`docker/start.sh` sekarang menjalankan:

- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`
- `php artisan migrate --force` bila `RUN_MIGRATIONS=true`

Route `/style.css` sudah memakai controller, bukan closure, sehingga `route:cache` aman.

## Database Index

Migration `2026_05_31_010000_add_production_performance_indexes.php` menambahkan index terukur untuk:

- Booking: daftar terbaru, seat lookup, laporan tanggal/status/pembayaran, dan partial index PostgreSQL untuk booking aktif.
- Carter: filter status, payment, BOP, tanggal, unit, dan armada.
- Bagasi: filter status/payment/tanggal, resi, pengirim/penerima, dan relasi keberangkatan.
- Jadwal/rute/unit/armada/customer: index untuk dropdown, filter, sorting, dan join utama.

Index search `%keyword%` tetap terbatas oleh B-tree. Jika data sudah sangat besar, pertimbangkan PostgreSQL `pg_trgm` untuk kolom nama/phone/alamat, tetapi itu sengaja belum diaktifkan agar migration tetap konservatif.

## Inertia Payload

Props shared `auth.user` sudah dipangkas ke field yang dipakai UI. Halaman booking memakai closure props sehingga partial reload bisa mengambil hanya `bookingGroups`, `bookingRouteOptions`, `latestBookings`, `totals`, dan `serverNow` setelah aksi tertentu. Laporan Admin Ops sekarang mengirim baris detail per halaman, sementara summary tetap menghitung seluruh periode.
