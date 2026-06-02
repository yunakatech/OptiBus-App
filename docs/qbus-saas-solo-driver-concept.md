# Ringkasan Konsep SaaS Qbus dan Qbus Solo Driver

## Visi Produk

Qbus dikembangkan sebagai SaaS operasional transportasi berbasis cloud untuk membantu pelaku transportasi mengelola order, jadwal, customer, kendaraan, pembayaran, dan laporan dari satu sistem.

Produk SaaS utama:

```text
Qbus Cloud
```

Tagline:

```text
Sistem operasional travel, carter, dan bagasi dalam satu dashboard.
```

## Struktur SaaS

Layer utama SaaS:

```text
Tenant / Perusahaan
-> Pool / Perwakilan
-> Rute Induk
-> Armada / Driver
-> Booking / Carter / Bagasi
-> Laporan
```

Setiap data operasional idealnya memiliki `tenant_id`, lalu data cabang/perwakilan menggunakan `pool_id`.

Urutan akses data:

```text
Tenant Scope -> Pool Scope -> Role Permission
```

## Target Paket Awal

Paket awal yang akan dibuat lebih dulu:

```text
Qbus Solo Driver
```

Paket ini ditujukan untuk driver mobil daerah yang memakai kendaraan pribadi untuk jasa transportasi, carter/private trip, antar jemput, dan kirim barang ringan.

## Qbus Solo Driver

Fokus utama Solo Driver:

- Mencatat order penumpang.
- Mencatat carter/private trip.
- Mencatat kiriman barang/bagasi ringan.
- Menyimpan data customer.
- Mengatur jadwal pribadi.
- Mencatat pembayaran, DP, dan pelunasan.
- Melihat laporan pendapatan harian dan bulanan.
- Dipakai dari HP sebagai PWA.

## Menu Solo Driver

Menu yang disarankan:

- Dashboard.
- Order Trip.
- Carter / Private Trip.
- Barang / Bagasi.
- Customer.
- Kendaraan Saya.
- Laporan.
- Pengaturan.

Menu kompleks seperti Pool, Role Permission detail, multi armada, dan Admin Ops besar tidak perlu ditampilkan pada mode Solo Driver.

## Dashboard Solo Driver

Dashboard dibuat sederhana dan mobile-first.

Isi utama:

- Order hari ini.
- Jadwal berikutnya.
- Pendapatan hari ini.
- Pendapatan bulan ini.
- Total belum lunas.
- Reminder order terdekat.
- Shortcut Tambah Order, Tambah Carter, Tambah Barang.

## Order Trip

Untuk order reguler sederhana tanpa peta kursi kompleks.

Field utama:

- Nama customer.
- Nomor HP.
- Tanggal.
- Jam.
- Asal jemput.
- Tujuan.
- Jumlah penumpang.
- Harga.
- DP.
- Status pembayaran.
- Status trip.
- Catatan.

Fitur:

- Copy detail order ke WhatsApp.
- Tandai lunas.
- Tandai selesai.
- Cancel order.
- Riwayat order.

## Carter / Private Trip

Untuk sewa mobil atau perjalanan private.

Field utama:

- Nama customer.
- Nomor HP.
- Tanggal mulai.
- Tanggal selesai.
- Jam berangkat.
- Asal jemput.
- Tujuan.
- Jenis layanan.
- Harga carter.
- Biaya operasional / BOP.
- DP.
- Status pembayaran.
- Catatan.

Fitur:

- Template rute carter favorit.
- Auto isi harga dari template.
- Copy detail carter.
- Share invoice.
- Tandai selesai.
- History carter.

## Barang / Bagasi

Untuk kiriman barang ringan.

Field utama:

- Nama pengirim.
- HP pengirim.
- Alamat pengirim.
- Nama penerima.
- HP penerima.
- Alamat penerima.
- Tanggal.
- Tujuan.
- Jumlah barang.
- Biaya kirim.
- Status pembayaran.
- Status barang.
- Catatan.

Fitur:

- Nomor resi otomatis sederhana.
- Copy/share resi ke WhatsApp.
- Tracking status manual.
- History barang.

## Customer

Customer otomatis tersimpan dari Order, Carter, dan Barang.

Data customer:

- Nama.
- Nomor HP.
- Alamat/pickup favorit.
- Catatan.
- Total transaksi.
- Terakhir transaksi.

Fitur:

- Search customer saat input.
- Auto isi nomor HP dan alamat.
- Riwayat transaksi customer.

## Kendaraan Saya

Karena Solo Driver hanya untuk 1 driver dan 1 kendaraan.

Data:

- Nama driver.
- Nomor HP driver.
- Plat nomor.
- Merk mobil.
- Tahun.
- Warna.
- Kapasitas penumpang.
- Biaya tetap bulanan.
- Dokumen kendaraan opsional.

## Laporan Solo Driver

Filter laporan:

- Hari ini.
- Minggu ini.
- Bulan ini.
- Custom tanggal.

Ringkasan:

- Pendapatan Order Trip.
- Pendapatan Carter.
- Pendapatan Barang.
- Total pendapatan.
- Total DP masuk.
- Total belum lunas.
- Total BOP.
- Biaya tetap kendaraan.
- Net income.

Breakdown:

- Per tanggal.
- Per jenis order.
- Per customer.
- Status pembayaran.

## Status

Status pembayaran:

```text
Belum Lunas
Lunas
```

Status trip:

```text
Terjadwal
Selesai
Cancel
```

Status barang:

```text
Diterima
Dalam Perjalanan
Tiba
Cancel
```

## Rekomendasi Data

Untuk MVP Solo Driver, gunakan table khusus agar UI dan logic tidak terlalu tercampur dengan mode perusahaan besar.

Table yang disarankan:

```text
solo_profiles
solo_vehicles
solo_orders
solo_charters
solo_luggages
solo_customers
solo_route_templates
solo_settings
```

Jika nanti Solo Driver masuk ke SaaS penuh, setiap table bisa diberi:

```text
tenant_id
user_id
plan_type = solo_driver
```

## Paket dan Harga

Rekomendasi paket:

```text
Qbus Solo Driver
```

Limit:

- 1 user.
- 1 driver.
- 1 kendaraan.
- 1 lokasi/pool default.
- Unlimited transaksi ringan.

Harga awal yang disarankan:

```text
Trial 14 hari: Gratis
Bulanan: Rp 59.000
Tahunan: Rp 599.000
```

## MVP Yang Dibuat Dulu

Tahap pertama:

- Dashboard Solo.
- Order Trip.
- Carter / Private Trip.
- Barang / Bagasi.
- Customer.
- Kendaraan Saya.
- Laporan sederhana.

Ditunda dulu:

- Billing otomatis.
- Multi tenant penuh.
- Custom domain.
- Payment gateway.
- Reminder dokumen kendaraan.
- Template WhatsApp custom.
- Integrasi WhatsApp API.

## Prinsip UI

- Mobile-first.
- Form pendek.
- Tombol besar.
- Default tanggal hari ini.
- Search customer otomatis.
- Copy/share WhatsApp selalu tersedia.
- Dashboard langsung menampilkan jadwal terdekat.

