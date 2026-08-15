# Mayar Payment Gateway Setup

## Scope

Implementasi Mayar di OptiBus dipakai untuk billing SaaS/subscription tenant.
Flow ini tidak mengubah payment operasional booking, charter, atau luggage.

## Environment Variables

Tambahkan nilai berikut ke `.env`:

```env
MAYAR_ENABLED=true
MAYAR_ENV=production
MAYAR_API_KEY=your_mayar_api_key
MAYAR_API_URL=
MAYAR_PAYMENT_CREATE_PATH=/invoices/create
MAYAR_WEBHOOK_SECRET=
MAYAR_TIMEOUT=15
```

### Vercel

Gunakan [vercel.env.example](../vercel.env.example) sebagai daftar variabel,
lalu tambahkan variabel tersebut di **Vercel Project -> Settings -> Environment
Variables**. Isi minimal `MAYAR_API_KEY`, kemudian set `MAYAR_ENABLED=true`.
Jangan memasukkan API key atau webhook secret ke Git atau ke `vercel.json`.

Aktifkan untuk environment `Production` dan `Preview` sesuai kebutuhan. Setelah
mengubah environment variable, lakukan redeploy agar konfigurasi baru terbaca.

### Catatan

- `MAYAR_API_KEY` wajib diisi agar checkout bisa dibuat.
- `MAYAR_ENV=production` memakai API V2 `https://api.mayar.id/hl/v2`; gunakan
  `sandbox` untuk `https://api.mayar.io/hl/v2`.
- `MAYAR_PAYMENT_CREATE_PATH` default memakai `/invoices/create`.
- `MAYAR_WEBHOOK_SECRET` disiapkan sebagai konfigurasi opsional jika workspace Mayar Anda memakainya.

## Webhook URL

Daftarkan webhook Mayar ke endpoint berikut:

```text
POST https://your-domain.example/api/webhooks/mayar
```

Webhook ini dipakai untuk event pembayaran, terutama `payment.received`. Sistem
memverifikasi `data.transactionId` ke endpoint detail transaksi Mayar sebelum
menandai invoice lunas.

## Cara Kerja

1. Tenant memilih paket di halaman `subscription`.
2. OptiBus membuat invoice internal.
3. Sistem memanggil Mayar untuk membuat checkout link.
4. User menyelesaikan pembayaran di Mayar.
5. Mayar mengirim webhook.
6. Invoice ditandai lunas dan subscription/tenant diaktifkan otomatis.

## Dashboard Admin

Halaman `Admin Ops -> SaaS -> Payment Gateway` menampilkan:

- status konfigurasi Mayar,
- endpoint API yang dipakai,
- webhook URL yang harus didaftarkan,
- status apakah secret/config sudah terisi.

## Verification Checklist

- Checkout subscription mengarah ke link Mayar.
- Invoice yang sukses dibayar berubah ke status `paid`.
- Duplicate webhook tidak memperpanjang subscription dua kali.
- Halaman SaaS admin menampilkan status gateway yang benar.
