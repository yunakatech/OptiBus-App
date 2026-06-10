# SaaS Plan Qbus

## 1. Ringkasan Produk

**Qbus** adalah platform SaaS untuk membantu driver, pemilik travel kecil, dan operator mobil lintas daerah dalam mengelola booking seat, booking bagasi dan carter, jadwal keberangkatan, jadwal carter, data pelanggan, status pembayaran, manifest perjalanan, dan laporan pendapatan.

Qbus dibuat untuk menyelesaikan masalah umum driver mobil lintas daerah yang masih mencatat booking secara manual melalui WhatsApp, buku catatan, atau spreadsheet sederhana.

---

## 2. Target Pengguna

### 2.1 Driver Individu

Driver yang menggunakan mobil pribadi sebagai kendaraan transportasi lintas daerah.

Contoh kendaraan:

- Avanza
- Xenia
- Innova
- Hiace
- Elf kecil
- Mobil pribadi lintas kabupaten/kota

Kebutuhan utama:

- Mencatat booking penumpang
- Menghindari double booking seat
- Melihat daftar penumpang sebelum berangkat
- Mencatat uang masuk
- Mencatat barang titipan

### 2.2 Pemilik Travel Kecil

Pemilik usaha travel dengan 2 sampai 10 kendaraan.

Kebutuhan utama:

- Mengelola beberapa driver
- Melihat laporan per kendaraan
- Melihat laporan per driver
- Mengontrol pendapatan harian dan bulanan
- Membagi akses untuk admin, driver, dan owner

### 2.3 Agen atau Admin Booking

Orang yang membantu menerima booking dari pelanggan.

Kebutuhan utama:

- Input data booking
- Mengatur seat
- Mengirim konfirmasi ke pelanggan
- Melihat jadwal dan ketersediaan seat
- Tidak perlu melihat seluruh laporan owner

---

## 3. Masalah yang Diselesaikan

1. Booking masih dicatat manual.
2. Data penumpang tercecer di WhatsApp.
3. Seat bisa double booking.
4. Driver sulit mengetahui total pendapatan.
5. Pemilik kendaraan sulit memantau pendapatan driver.
6. Barang titipan tidak tercatat rapi.
7. Tidak ada database pelanggan.
8. Tidak ada laporan harian, mingguan, dan bulanan.
9. Tidak ada manifest penumpang yang siap cetak.
10. Tidak ada sistem langganan untuk operasional driver kecil.

---

## 4. Value Proposition

Qbus membantu driver dan travel kecil berpindah dari pencatatan manual ke sistem digital sederhana.

> Qbus membantu driver mobil lintas daerah mencatat booking seat, bagasi, pembayaran, dan pendapatan secara rapi dalam satu platform berlangganan bulanan.

Slogan:

> Dari catatan WhatsApp dan buku tulis, pindah ke sistem booking rapi dengan Qbus.

---

## 5. Modul Utama SaaS Qbus

## 5.1 Modul Auth dan Akun

Fitur:

- Register akun driver
- Login pengguna
- Reset password
- Verifikasi email atau nomor WhatsApp
- Profil usaha/travel
- Profil kendaraan
- Role pengguna

Role awal:

- Owner
- Admin
- Driver

Role lanjutan:

- Agen
- Finance
- Super Admin Qbus

---

## 5.2 Modul Subscription SaaS

Fitur:

- Paket langganan bulanan
- Status langganan aktif/nonaktif
- Tanggal mulai langganan
- Tanggal berakhir langganan
- Invoice langganan
- Riwayat pembayaran
- Upgrade paket
- Downgrade paket
- Grace period setelah langganan habis
- Pembatasan fitur sesuai paket

Status subscription:

- Trial
- Active
- Past Due
- Suspended
- Cancelled

Alur subscription:

1. User daftar akun.
2. User mendapatkan trial gratis.
3. User memilih paket.
4. User membayar langganan.
5. Sistem mengaktifkan fitur sesuai paket.
6. Jika masa aktif habis, akun masuk grace period.
7. Jika tidak diperpanjang, akses dibatasi.

---

## 5.3 Modul Kendaraan

Fitur:

- Tambah kendaraan
- Edit kendaraan
- Hapus kendaraan
- Nomor plat kendaraan
- Jenis kendaraan
- Kapasitas seat
- Nama driver utama
- Status kendaraan aktif/nonaktif

Data kendaraan:

- Nama kendaraan
- Nomor polisi
- Jenis kendaraan
- Kapasitas seat
- Foto kendaraan
- Catatan kendaraan

---

## 5.4 Modul Rute

Fitur:

- Tambah rute
- Edit rute
- Hapus rute
- Kota asal
- Kota tujuan
- Titik jemput default
- Titik turun default
- Harga tiket default
- Estimasi durasi perjalanan

Contoh rute:

- Pinrang → Makassar
- Makassar → Pinrang
- Parepare → Makassar
- Bone → Makassar
- Toraja → Makassar

---

## 5.5 Modul Jadwal Keberangkatan

Fitur:

- Buat jadwal perjalanan
- Pilih kendaraan
- Pilih driver
- Pilih rute
- Tanggal berangkat
- Jam berangkat
- Harga tiket
- Kapasitas seat
- Status perjalanan

Status perjalanan:

- Draft
- Open Booking
- Full Booked
- On Trip
- Completed
- Cancelled

---

## 5.6 Modul Booking Seat

Fitur:

- Tambah booking penumpang
- Pilih jadwal
- Pilih nomor seat
- Input nama penumpang
- Input nomor WhatsApp
- Input titik jemput
- Input titik turun
- Input harga
- Input status pembayaran
- Catatan khusus
- Ubah seat
- Batalkan booking

Status booking:

- Booking
- Confirmed
- Paid
- Checked In
- Cancelled
- No Show

Data booking:

- Nama penumpang
- Nomor WhatsApp
- Jadwal
- Rute
- Nomor seat
- Titik jemput
- Titik turun
- Harga tiket
- Status pembayaran
- Metode pembayaran
- Catatan

---

## 5.7 Modul Seat Map

Fitur:

- Tampilan kursi visual
- Seat kosong
- Seat booking
- Seat lunas
- Seat batal
- Seat tidak tersedia
- Auto-update seat setelah booking
- Proteksi double booking

Status seat:

- Available
- Reserved
- Paid
- Blocked
- Cancelled

Contoh tampilan sederhana:

| Seat | Status |
|---|---|
| 1 | Paid |
| 2 | Available |
| 3 | Reserved |
| 4 | Available |
| 5 | Paid |
| 6 | Available |
| 7 | Reserved |

---

## 5.8 Modul Bagasi dan Barang Titipan

Fitur:

- Tambah booking barang
- Input pengirim
- Input penerima
- Nomor WhatsApp pengirim
- Nomor WhatsApp penerima
- Jenis barang
- Berat atau ukuran barang
- Biaya pengiriman
- Status pembayaran
- Status barang
- Kode resi sederhana

Status barang:

- Waiting Pickup
- Picked Up
- On Trip
- Arrived
- Delivered
- Cancelled

---

## 5.9 Modul Pembayaran Penumpang

Fitur:

- Catat pembayaran tunai
- Catat pembayaran transfer
- Catat DP
- Catat belum bayar
- Catat bayar di tempat
- Upload bukti transfer
- Riwayat pembayaran

Status pembayaran:

- Unpaid
- DP
- Paid
- Refund
- Cancelled

Metode pembayaran:

- Cash
- Transfer Bank
- QRIS
- E-wallet
- Bayar di Tempat

---

## 5.10 Modul Manifest Perjalanan

Fitur:

- Generate manifest otomatis
- Daftar penumpang per jadwal
- Daftar barang per jadwal
- Print manifest
- Export PDF
- Share ke WhatsApp
- Filter berdasarkan status pembayaran

Isi manifest:

- Nama travel
- Nama driver
- Nomor kendaraan
- Rute
- Tanggal dan jam berangkat
- Daftar penumpang
- Nomor seat
- Titik jemput
- Titik turun
- Status pembayaran
- Daftar barang titipan

---

## 5.11 Modul Laporan Pendapatan

Fitur:

- Laporan harian
- Laporan mingguan
- Laporan bulanan
- Laporan per driver
- Laporan per kendaraan
- Laporan per rute
- Laporan tiket
- Laporan bagasi
- Laporan unpaid
- Export Excel
- Export PDF

Data laporan:

- Total booking seat
- Total seat terjual
- Total pendapatan tiket
- Total pendapatan bagasi
- Total pembayaran lunas
- Total belum bayar
- Total pembatalan
- Pendapatan bersih

---

## 5.12 Modul Pelanggan

Fitur:

- Database pelanggan otomatis
- Riwayat perjalanan pelanggan
- Nomor WhatsApp pelanggan
- Catatan pelanggan
- Pelanggan sering booking
- Export data pelanggan

Data pelanggan:

- Nama
- Nomor WhatsApp
- Kota asal
- Kota tujuan favorit
- Jumlah perjalanan
- Total transaksi
- Catatan

---

## 5.13 Modul WhatsApp

Tahap awal menggunakan WhatsApp link/manual.

Fitur awal:

- Generate pesan booking
- Generate pesan konfirmasi pembayaran
- Generate pesan reminder keberangkatan
- Generate pesan pembatalan
- Generate pesan barang sampai

Contoh pesan booking:

```text
Halo {{nama_penumpang}}, booking Anda berhasil.
Rute: {{rute}}
Tanggal: {{tanggal}}
Jam: {{jam}}
Seat: {{seat}}
Titik Jemput: {{jemput}}
Harga: {{harga}}
Terima kasih telah menggunakan {{nama_travel}}.
```

Fitur lanjutan:

- WhatsApp API
- Reminder otomatis
- Notifikasi pembayaran
- Broadcast jadwal kosong
- Bot cek ketersediaan seat

---

## 5.14 Modul Halaman Booking Online

Setiap driver atau travel mendapatkan halaman publik.

Contoh URL:

```text
qbus.id/mandiritrans
qbus.id/travel/mandiritrans
```

Isi halaman:

- Logo travel
- Nama travel
- Rute tersedia
- Jadwal keberangkatan
- Seat tersedia
- Harga tiket
- Form booking
- Tombol WhatsApp
- Info kontak

Fitur booking online:

1. Pelanggan pilih rute.
2. Pelanggan pilih jadwal.
3. Pelanggan pilih seat.
4. Pelanggan isi nama dan nomor WhatsApp.
5. Pelanggan kirim booking.
6. Driver menerima notifikasi.
7. Driver melakukan konfirmasi.

---

## 5.15 Modul Notifikasi

Fitur:

- Notifikasi booking baru
- Notifikasi pembayaran
- Notifikasi seat penuh
- Notifikasi perjalanan hari ini
- Notifikasi subscription hampir habis
- Notifikasi subscription expired

Channel notifikasi:

- In-app notification
- Email
- WhatsApp manual link
- WhatsApp API pada tahap lanjutan

---

## 5.16 Modul Admin Qbus

Digunakan oleh pengelola platform Qbus.

Fitur:

- Kelola tenant/user
- Kelola paket langganan
- Kelola pembayaran subscription
- Aktivasi akun manual
- Suspend akun
- Lihat statistik platform
- Lihat jumlah booking seluruh tenant
- Lihat MRR
- Lihat churn
- Lihat user aktif

Metric SaaS:

- MRR
- ARR
- Active Tenant
- Churn Rate
- Trial Conversion Rate
- ARPU
- Total Booking
- Total Revenue Processed

---

## 6. Paket Langganan

## 6.1 Qbus Starter

Target:

- Driver individu
- 1 kendaraan
- Rute terbatas

Harga rekomendasi:

- Rp49.000/bulan

Fitur:

- 1 kendaraan
- 2 rute
- Booking seat
- Booking bagasi
- Laporan harian
- Manifest perjalanan
- WhatsApp template manual
- 1 user

---

## 6.2 Qbus Pro

Target:

- Driver aktif
- Travel kecil
- Butuh laporan lebih lengkap

Harga rekomendasi:

- Rp99.000/bulan

Fitur:

- Hingga 3 kendaraan
- Rute tidak terbatas
- Booking seat
- Booking bagasi
- Seat map
- Laporan harian, mingguan, bulanan
- Export PDF
- Export Excel
- Halaman booking online
- Database pelanggan
- Hingga 3 user

---

## 6.3 Qbus Fleet

Target:

- Pemilik beberapa kendaraan
- Travel lokal
- Operator armada kecil

Harga rekomendasi:

- Rp199.000 sampai Rp299.000/bulan

Fitur:

- Hingga 10 kendaraan
- Banyak driver
- Banyak admin
- Multi-role user
- Laporan per kendaraan
- Laporan per driver
- Laporan per rute
- Dashboard owner
- Halaman booking online
- Export PDF dan Excel
- Prioritas support

---

## 6.4 Add-on Berbayar

| Add-on | Harga Rekomendasi | Keterangan |
|---|---:|---|
| WhatsApp Reminder Otomatis | Rp49.000/bulan | Reminder booking dan keberangkatan |
| Tambahan Kendaraan | Rp20.000/kendaraan/bulan | Untuk fleet yang berkembang |
| Custom Domain | Rp50.000/bulan | Contoh booking.namatravel.com |
| Advanced Report | Rp75.000/bulan | Laporan detail dan grafik |
| Branding Halaman Booking | Rp50.000/bulan | Hilangkan branding Qbus |

---

## 7. Roadmap Pengembangan

## 7.1 Phase 1 — MVP

Tujuan:

Menguji apakah driver mau menggunakan dan membayar Qbus.

Fitur MVP:

1. Login/register
2. Profil driver/travel
3. Data kendaraan
4. Data rute
5. Jadwal keberangkatan
6. Booking seat
7. Seat status
8. Booking bagasi
9. Status pembayaran
10. Manifest perjalanan
11. Laporan pendapatan sederhana
12. Subscription manual
13. WhatsApp template manual

Output MVP:

- Driver bisa mencatat booking
- Driver bisa melihat seat kosong dan terisi
- Driver bisa melihat pendapatan
- Driver bisa membuat manifest
- Driver bisa membayar langganan bulanan

---

## 7.2 Phase 2 — SaaS Monetization

Tujuan:

Membuat sistem langganan berjalan lebih otomatis.

Fitur:

1. Paket langganan
2. Trial 7 sampai 14 hari
3. Invoice otomatis
4. Pembayaran via QRIS/payment gateway
5. Grace period
6. Pembatasan fitur sesuai paket
7. Dashboard admin Qbus
8. Reminder subscription expired

---

## 7.3 Phase 3 — Public Booking Page

Tujuan:

Membantu driver mendapatkan booking dari link publik.

Fitur:

1. Halaman publik travel
2. Form booking penumpang
3. Form booking barang
4. Pilih jadwal
5. Pilih seat
6. Konfirmasi booking via WhatsApp
7. Admin approval booking

---

## 7.4 Phase 4 — Fleet Management

Tujuan:

Melayani pemilik armada yang memiliki banyak kendaraan dan driver.

Fitur:

1. Multi kendaraan
2. Multi driver
3. Role owner/admin/driver
4. Laporan per driver
5. Laporan per kendaraan
6. Laporan komisi driver
7. Monitoring performa armada

---

## 7.5 Phase 5 — Automation dan Marketplace

Tujuan:

Mengembangkan Qbus menjadi ekosistem booking transportasi lokal.

Fitur:

1. WhatsApp API
2. Reminder otomatis
3. Payment gateway untuk penumpang
4. Rating pelanggan
5. Marketplace rute travel
6. Search travel berdasarkan rute
7. Komisi per transaksi
8. Integrasi maps

---

## 8. Prioritas Fitur Berdasarkan Dampak

## High Priority

1. Booking seat
2. Seat map
3. Jadwal keberangkatan
4. Status pembayaran
5. Laporan pendapatan
6. Booking bagasi
7. Manifest perjalanan
8. Subscription driver

## Medium Priority

1. Halaman booking online
2. Export PDF
3. Export Excel
4. Database pelanggan
5. Multi-user
6. Multi-kendaraan
7. Dashboard owner

## Low Priority

1. WhatsApp API
2. Marketplace publik
3. Rating penumpang
4. Loyalty pelanggan
5. Promo dan voucher
6. Dynamic pricing
7. Integrasi maps tingkat lanjut

---

## 9. Struktur Database Awal

Tabel utama:

- users
- tenants
- subscriptions
- plans
- vehicles
- drivers
- routes
- schedules
- seats
- bookings
- passengers
- baggage_orders
- payments
- manifests
- notifications
- audit_logs

---

## 10. Contoh Entity Sederhana

## 10.1 Tenant

```json
{
  "id": "tenant_001",
  "business_name": "Mandiri Trans",
  "owner_name": "Andi",
  "phone": "0852xxxx",
  "subscription_status": "active",
  "plan": "pro"
}
```

## 10.2 Vehicle

```json
{
  "id": "vehicle_001",
  "tenant_id": "tenant_001",
  "name": "Avanza Putih",
  "plate_number": "DD 1234 XX",
  "seat_capacity": 7,
  "status": "active"
}
```

## 10.3 Schedule

```json
{
  "id": "schedule_001",
  "tenant_id": "tenant_001",
  "vehicle_id": "vehicle_001",
  "route": "Pinrang - Makassar",
  "departure_date": "2026-05-20",
  "departure_time": "08:00",
  "status": "open_booking"
}
```

## 10.4 Booking

```json
{
  "id": "booking_001",
  "schedule_id": "schedule_001",
  "passenger_name": "Rina",
  "passenger_phone": "0852xxxx",
  "seat_number": "3",
  "pickup_point": "Alun-alun Pinrang",
  "dropoff_point": "Daya Makassar",
  "ticket_price": 150000,
  "payment_status": "paid",
  "booking_status": "confirmed"
}
```

## 10.5 Baggage Order

```json
{
  "id": "bag_001",
  "schedule_id": "schedule_001",
  "sender_name": "Ayu",
  "sender_phone": "0853xxxx",
  "receiver_name": "Budi",
  "receiver_phone": "0812xxxx",
  "item_description": "Kardus kecil",
  "shipping_fee": 50000,
  "payment_status": "paid",
  "delivery_status": "on_trip"
}
```

---

## 11. User Flow Utama

## 11.1 Flow Driver Membuat Jadwal

1. Driver login.
2. Driver masuk menu Jadwal.
3. Driver klik Buat Jadwal.
4. Driver pilih rute.
5. Driver pilih kendaraan.
6. Driver isi tanggal dan jam berangkat.
7. Driver simpan jadwal.
8. Sistem membuat seat sesuai kapasitas kendaraan.
9. Jadwal siap menerima booking.

## 11.2 Flow Booking Penumpang

1. Penumpang menghubungi driver via WhatsApp.
2. Driver membuka jadwal di Qbus.
3. Driver memilih seat kosong.
4. Driver mengisi data penumpang.
5. Driver memilih status pembayaran.
6. Driver menyimpan booking.
7. Sistem menandai seat sebagai terisi.
8. Sistem membuat pesan konfirmasi.
9. Driver mengirim pesan ke penumpang via WhatsApp.

## 11.3 Flow Booking Bagasi

1. Pengirim menghubungi driver.
2. Driver membuka menu Bagasi.
3. Driver memilih jadwal perjalanan.
4. Driver mengisi data pengirim dan penerima.
5. Driver mengisi deskripsi barang.
6. Driver mengisi biaya kirim.
7. Driver menyimpan data.
8. Sistem membuat kode barang/resi sederhana.
9. Driver mengirim konfirmasi ke pengirim.

## 11.4 Flow Laporan Pendapatan

1. Driver menyelesaikan perjalanan.
2. Sistem menghitung pendapatan seat.
3. Sistem menghitung pendapatan bagasi.
4. Sistem memisahkan lunas dan belum bayar.
5. Driver melihat laporan harian.
6. Owner melihat laporan bulanan.

---

## 12. Fitur Pembatasan Berdasarkan Paket

| Fitur | Starter | Pro | Fleet |
|---|---:|---:|---:|
| Kendaraan | 1 | 3 | 10 |
| Rute | 2 | Unlimited | Unlimited |
| User | 1 | 3 | 10 |
| Booking Seat | Ya | Ya | Ya |
| Booking Bagasi | Ya | Ya | Ya |
| Laporan Harian | Ya | Ya | Ya |
| Laporan Bulanan | Tidak | Ya | Ya |
| Export PDF | Tidak | Ya | Ya |
| Export Excel | Tidak | Ya | Ya |
| Halaman Booking Online | Tidak | Ya | Ya |
| Multi Driver | Tidak | Terbatas | Ya |
| Dashboard Owner | Tidak | Terbatas | Ya |

---

## 13. Strategi Go-To-Market

## 13.1 Target Awal

Mulai dari daerah yang banyak memiliki mobil lintas daerah.

Contoh:

- Sulawesi Selatan
- Sulawesi Barat
- Sulawesi Tenggara
- Kalimantan
- Nusa Tenggara
- Sumatera rute antar kabupaten

## 13.2 Strategi Akuisisi

1. Datangi driver travel lokal langsung.
2. Tawarkan trial gratis 14 hari.
3. Bantu input data rute dan kendaraan pertama.
4. Buatkan halaman booking sederhana.
5. Tunjukkan laporan pendapatan otomatis.
6. Gunakan testimoni driver awal.
7. Promosi di grup Facebook/WhatsApp komunitas driver.

## 13.3 Penawaran Awal

- Gratis setup
- Trial 14 hari
- Harga early adopter Rp29.000/bulan
- Bantuan input data awal
- Bonus halaman booking online

---

## 14. KPI Produk

Metric yang perlu dipantau:

1. Jumlah driver terdaftar
2. Jumlah driver aktif mingguan
3. Jumlah booking per hari
4. Jumlah jadwal dibuat
5. Total pendapatan tercatat
6. Jumlah subscription aktif
7. Trial to paid conversion
8. Churn bulanan
9. Average revenue per user
10. Jumlah booking bagasi

---

## 15. Risiko dan Solusi

| Risiko | Solusi |
|---|---|
| Driver tidak terbiasa pakai aplikasi | Buat UI sangat sederhana dan mobile-first |
| Driver tetap nyaman pakai WhatsApp | Integrasikan Qbus dengan alur WhatsApp manual |
| Driver keberatan bayar | Berikan trial dan paket murah |
| Data tidak diinput konsisten | Buat input booking super cepat |
| Internet tidak stabil | Buat halaman ringan dan cache data penting |
| Persaingan marketplace besar | Fokus ke operasional driver kecil, bukan marketplace dulu |

---

## 16. Prinsip Desain Produk

1. Mobile-first
2. Cepat digunakan
3. Tidak ribet
4. Bahasa sederhana
5. Mirip kebiasaan driver di WhatsApp
6. Bisa dipakai tanpa training panjang
7. Laporan mudah dipahami
8. Fokus pada masalah nyata di lapangan

---

## 17. Rekomendasi Teknologi

Frontend:

- Next.js
- React
- Tailwind CSS

Backend:

- Laravel
- Node.js
- Supabase
- NestJS

Database:

- PostgreSQL
- MySQL

Authentication:

- Supabase Auth
- Firebase Auth
- Custom Auth

Payment Gateway:

- Midtrans
- Xendit
- Duitku

File Export:

- PDF manifest
- Excel laporan

Hosting:

- Vercel untuk frontend
- Railway/Render/DigitalOcean untuk backend
- Supabase untuk database cepat MVP

---

## 18. Struktur Menu Aplikasi

Menu untuk Driver:

1. Dashboard
2. Jadwal
3. Booking Seat
4. Bagasi
5. Penumpang
6. Laporan
7. Kendaraan
8. Rute
9. Langganan
10. Pengaturan

Menu untuk Owner:

1. Dashboard Owner
2. Kendaraan
3. Driver
4. Jadwal
5. Booking
6. Bagasi
7. Laporan Armada
8. User Management
9. Subscription
10. Pengaturan

Menu untuk Admin Qbus:

1. Dashboard Platform
2. Tenant
3. User
4. Subscription
5. Payment
6. Paket
7. Statistik
8. Support

---

## 19. Ide Pengembangan Lanjutan

1. Aplikasi Android driver
2. Aplikasi pelanggan
3. Marketplace rute travel
4. Sistem komisi agen
5. QR check-in penumpang
6. QR resi barang
7. Integrasi Google Maps
8. Estimasi waktu tiba
9. Live tracking kendaraan
10. Review dan rating driver
11. Promo dan voucher
12. Pembayaran online penumpang
13. Auto settlement ke owner
14. Fitur absensi driver
15. Maintenance kendaraan

---

## 20. Kesimpulan

Qbus sebaiknya dibangun secara bertahap sebagai SaaS operasional untuk driver mobil lintas daerah dan travel kecil.

Fokus pertama bukan marketplace besar, tetapi sistem sederhana untuk:

- Mencatat booking seat
- Mengelola bagasi
- Menghindari double booking
- Membuat manifest
- Mencatat pembayaran
- Menampilkan laporan pendapatan
- Menjalankan langganan bulanan

MVP terbaik:

> Dashboard driver + jadwal keberangkatan + booking seat + booking bagasi + status pembayaran + manifest + laporan pendapatan + subscription bulanan.

Setelah MVP terbukti digunakan dan driver bersedia membayar, Qbus dapat dikembangkan menjadi platform yang lebih besar dengan halaman booking publik, WhatsApp API, fleet management, dan marketplace travel lokal.
