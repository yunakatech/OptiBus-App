# Cloudflare Migration Guide

Panduan ini menjelaskan status proyek OptiBus terhadap Cloudflare dan jalur migrasi yang realistis.

## Status Saat Ini

Repositori ini adalah aplikasi **Laravel 13 + Inertia + Svelte**. Artinya:

- backend masih bergantung pada runtime PHP/Laravel,
- halaman frontend menerima data dari controller Laravel via Inertia,
- sebagian besar fitur operasi, autentikasi, PDF, scheduler, dan webhook masih hidup di backend Laravel.

Cloudflare **tidak menjalankan PHP/Laravel secara langsung** di Pages atau Workers. Cloudflare Pages cocok untuk frontend statis dan framework seperti SvelteKit, Astro, Nuxt, Qwik, atau Solid Start, sedangkan Workers berjalan di runtime berbasis web API, bukan PHP.

Jadi, **project ini tidak bisa dipindah ke Cloudflare secara langsung tanpa migrasi arsitektur**.

## Jalur Yang Masuk Akal

### Opsi 1: Hybrid

- Laravel tetap di origin hosting seperti Render, Coolify, VPS, atau container.
- Cloudflare dipakai sebagai DNS, CDN, WAF, dan edge cache.
- Ini paling cepat dan paling aman karena tidak perlu rewrite backend.

### Opsi 2: Full Cloudflare Migration

- Frontend dipindah ke stack Cloudflare-friendly, misalnya SvelteKit.
- Backend API dipindah ke Cloudflare Workers atau service lain yang kompatibel.
- Storage, database, queue, PDF, dan cron perlu diganti dengan layanan yang didukung Cloudflare.

Opsi ini adalah jalur yang benar kalau targetnya benar-benar hosting penuh di Cloudflare, tetapi biayanya adalah **rewrite besar**.

## Apa Yang Perlu Dimigrasikan

Komponen berikut perlu dipetakan ulang sebelum benar-benar bisa jalan di Cloudflare:

- Auth session dan middleware Laravel.
- Inertia page rendering.
- Route/controller berbasis PHP.
- Export PDF dan headless browser flow.
- Scheduler/command harian.
- Queue/job processing.
- Upload file dan storage lokal.
- Webhook pembayaran dan endpoint API.

## Rekomendasi Tahapan Migrasi

### Fase 1: Audit Aplikasi

- Inventaris semua route di `routes/web.php`, `routes/settings.php`, dan `routes/console.php`.
- Tandai page yang masih bergantung pada Inertia props dari Laravel.
- Kelompokkan fitur menjadi public pages, dashboard, CRUD admin, dan background jobs.

### Fase 2: Pecah Lapisan Data

- Definisikan contract API untuk halaman-halaman utama.
- Pindahkan logika bisnis yang sekarang tersebar di controller ke service atau API layer yang jelas.
- Pisahkan data yang hanya untuk tampilan dari data yang harus tetap server-side.

### Fase 3: Bangun Frontend Cloudflare-Friendly

- Buat frontend baru berbasis SvelteKit atau framework yang didukung Pages.
- Replikasi UI halaman utama secara bertahap.
- Pastikan auth dan session mengikuti model runtime baru.

### Fase 4: Migrasi Backend

- Pindahkan endpoint yang diperlukan ke runtime yang bisa jalan di Cloudflare.
- Ganti storage, queue, dan scheduler dengan layanan yang kompatibel.
- Uji PDF, webhook, dan upload dengan asumsi edge/serverless.

### Fase 5: Deploy

- Login Cloudflare dengan `npx wrangler login`.
- Validasi akun dengan `npx wrangler whoami`.
- Deploy frontend ke Pages atau Worker sesuai arsitektur final.

## Kondisi Repo Saat Ini

Beberapa bagian repo ini masih sangat Laravel-centric:

- `app/` berisi controller, middleware, service, dan command Laravel.
- `resources/js/` memang Svelte, tetapi masih dikendalikan Inertia.
- `config/inertia.php` mengharapkan page component dari Laravel route response.

Itu sebabnya file ini tidak berisi `wrangler.toml` langsung. Menambahkan config deploy Cloudflare tanpa rewrite backend akan menyesatkan dan tidak akan menghasilkan aplikasi yang bisa jalan penuh.

## Langkah Berikutnya

Kalau kamu mau, kita bisa lanjut dengan salah satu jalur ini:

1. Siapkan hybrid deployment: Cloudflare di depan origin Laravel.
2. Mulai rewrite bertahap menuju SvelteKit + Cloudflare-native backend.

