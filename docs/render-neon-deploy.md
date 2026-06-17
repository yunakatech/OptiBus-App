# Render + Neon Deploy Guide

Panduan ini disusun untuk repo `yunakatech/OptiBus` dengan target hosting Render dan database Neon.

## 1. Arahkan remote Git ke repo GitHub baru

Jika local repo Anda masih menunjuk ke starter kit bawaan, ubah `origin` ke repo baru:

```bash
git remote set-url origin https://github.com/yunakatech/OptiBus.git
```

Verifikasi:

```bash
git remote -v
```

## 2. Commit dan push file deploy

```bash
git add Dockerfile .dockerignore docker/start.sh render.yaml docs/render-neon-deploy.md app/Support/HeadlessPdf.php
git commit -m "Add Render and Neon deployment setup"
git push -u origin main
```

## 3. Buat database di Neon

1. Login ke Neon.
2. Buat project baru di region `AWS Asia Pacific (Singapore)`.
3. Buat database, misalnya `cabooq`.
4. Buat role, misalnya `cabooq_app`.
5. Buka menu `Connect`, lalu salin connection string PostgreSQL.

Contoh format:

```env
postgresql://cabooq_app:your-password@ep-xxxxxx.ap-southeast-1.aws.neon.tech/cabooq?sslmode=require&channel_binding=require
```

Untuk setup awal ini, gunakan connection string direct dari Neon.

## 4. Deploy ke Render

Pilih salah satu dari dua cara berikut.

### Opsi A: Blueprint

1. Login ke Render.
2. Pilih `New` -> `Blueprint`.
3. Hubungkan repo `yunakatech/OptiBus`.
4. Render akan membaca `render.yaml`.
5. Saat diminta mengisi env yang `sync: false`, isi:

```env
APP_KEY=base64:...
APP_URL=https://<your-render-service>.onrender.com
DB_URL=postgresql://cabooq_app:your-password@ep-xxxxxx.ap-southeast-1.aws.neon.tech/cabooq?sslmode=require&channel_binding=require
```

### Opsi B: Manual Web Service

1. Pilih `New` -> `Web Service`.
2. Hubungkan repo `yunakatech/OptiBus`.
3. Pilih runtime `Docker`.
4. Region pilih `Singapore`.
5. Aktifkan auto deploy untuk branch `main`.
6. Health check path isi `/up`.
7. Tambahkan environment variables berikut:

```env
APP_NAME=OptiBus
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=https://<your-render-service>.onrender.com
APP_TIMEZONE=Asia/Makassar
LOG_CHANNEL=stack
LOG_STACK=stderr
LOG_LEVEL=info
DB_CONNECTION=pgsql
DB_URL=postgresql://cabooq_app:your-password@ep-xxxxxx.ap-southeast-1.aws.neon.tech/cabooq?sslmode=require&channel_binding=require
DB_SSLMODE=require
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
MAIL_MAILER=log
RUN_MIGRATIONS=true
```

## 5. Ambil APP_KEY Laravel

Jalankan lokal:

```bash
php artisan key:generate --show
```

Lalu pakai hasilnya untuk `APP_KEY` di Render.

## 6. Verifikasi hasil deploy

Periksa endpoint berikut:

- `/up` untuk health check
- `/` untuk landing page
- `/login` untuk auth flow

## 7. Catatan penting

- Setup ini memakai `php artisan serve` agar deploy Render gratis tetap sederhana.
- Jika nama service `OptiBus` sudah dipakai, ubah nama service saat membuat Web Service di Render lalu sesuaikan `APP_URL`.
- `QUEUE_CONNECTION` diset `sync` karena Render Free tidak ideal untuk worker terpisah.
- Export PDF sekarang sudah mendukung binary Linux lewat env `BROWSER_BINARY`, dan image Docker memasang Chromium.
- Penyimpanan lokal di Render bersifat ephemeral. Jangan simpan file penting permanen di disk lokal.
