# Konsep Mapping Pool, Rute Induk, Carter, Bagasi, dan Akses User

## Tujuan

Membuat data operasional Carter dan Bagasi dapat dimapping ke Perwakilan/Pool atau Rute Induk, sehingga akses user dan laporan bisa dibatasi sesuai area yang dikelola.

## Rekomendasi Utama

Gunakan Perwakilan/Pool sebagai scope akses utama.

Alur relasinya:

```text
User -> Perwakilan/Pool -> Rute Induk -> Booking / Carter / Bagasi
```

Rute Induk tetap menjadi master rute. Perwakilan/Pool menjadi pemilik operasional untuk akses data, laporan, target revenue, dan performa cabang.

## Struktur Data Yang Disarankan

- `pools`: master Perwakilan/Pool.
- `pool_routes`: mapping Perwakilan/Pool ke Rute Induk.
- `user_pools`: mapping user ke Perwakilan/Pool yang dikelola.
- `charters.pool_id`: pool pemilik data Carter.
- `charters.route_id` atau `charters.master_carter_id`: referensi rute/preset Carter bila tersedia.
- `luggages.pool_id`: pool pemilik data Bagasi.
- `luggages.route_id`: referensi Rute Induk untuk Bagasi.

Simpan `pool_id` langsung di tabel transaksi sebagai snapshot. Dengan begitu laporan historis tetap stabil walaupun mapping rute ke pool berubah di kemudian hari.

## Aturan Akses User

- Super Admin dapat melihat semua pool.
- User yang dimapping ke satu atau beberapa pool hanya dapat melihat data dari pool tersebut.
- User tanpa mapping pool tidak dapat melihat data operasional, kecuali role-nya memang diberi hak global.
- Filter data Booking, Carter, Bagasi, dan Laporan wajib mengikuti scope pool user login.

## Carter

Data Carter tetap wajib memiliki `pool_id`, tetapi tidak perlu ditampilkan di card agar UI tetap bersih.

Rekomendasi UI Carter:

- Card Carter tidak menampilkan info Pool/Perwakilan.
- Form tambah/edit Carter menampilkan field Perwakilan/Pool.
- Halaman detail Carter boleh menampilkan Perwakilan/Pool.
- Jika user hanya punya satu pool, field pool otomatis terisi.
- Jika user punya banyak pool atau Super Admin, field pool wajib dipilih.
- Jika rute/master Carter sudah punya mapping pool, field pool dapat otomatis terisi.

## Bagasi

Data Bagasi juga wajib memiliki `pool_id` dan sebaiknya memiliki `route_id`.

Rekomendasi UI Bagasi:

- Form tambah/edit Bagasi menampilkan field Perwakilan/Pool atau Rute Induk.
- Jika user hanya punya satu pool, field pool otomatis terisi.
- Jika user memilih Rute Induk yang sudah dimapping ke pool, pool dapat otomatis terisi.
- Data pengirim dan penerima tetap bebas, tetapi akses dan laporan mengikuti `pool_id`.

## Laporan

Laporan harus berbasis scope Pool/Perwakilan, lalu bisa di-breakdown ke Rute Induk dan jenis transaksi.

Filter laporan yang disarankan:

- Periode tanggal.
- Perwakilan/Pool.
- Rute Induk.
- Jenis laporan: Semua, Booking, Carter, Bagasi.
- Status pembayaran.
- Status perjalanan.

Ringkasan laporan Pool:

- Revenue Booking.
- Revenue Carter.
- Revenue Bagasi.
- Total Revenue.
- BOP Booking/Keberangkatan.
- BOP Carter.
- Total BOP.
- Gross Margin.
- Fixed Cost Pool.
- Net Margin.
- Target Revenue Pool.
- Achievement.
- Status target.

Breakdown ideal:

```text
Pool/Perwakilan
-> Rute Induk
-> Jenis Transaksi
-> Detail Transaksi
```

## Prinsip Implementasi

- Jangan hanya mengandalkan mapping rute saat query laporan.
- Simpan `pool_id` pada transaksi saat data dibuat.
- Terapkan scope pool di semua endpoint listing dan laporan.
- UI card tetap fokus ke data utama, bukan info internal mapping.
- Field pool dibuat otomatis bila user hanya mengelola satu pool.
- Super Admin tetap diberi pilihan filter pool untuk audit dan laporan menyeluruh.

