# Tahap Berikutnya Mobile Friendly Tabel OptiBus

Dokumen ini merangkum pekerjaan lanjutan agar semua submenu Pengaturan dan Laporan yang masih memakai tabel besar menjadi konsisten mobile-friendly.

## Tujuan

- Mengurangi kebutuhan horizontal scroll pada tampilan mobile.
- Membuat data penting mudah discan dalam bentuk card compact.
- Menjaga tabel desktop tetap lengkap untuk pekerjaan operasional di layar besar.
- Menyamakan pola filter, pagination, dan aksi row di semua submenu.
- Membuat pengalaman PWA/iPhone lebih nyaman saat berpindah data dan melakukan aksi.

## Status Eksekusi 2026-06-03

- Rute Induk sudah memakai card list compact di mobile dan tabel lengkap di desktop.
- Driver sudah memakai card list compact di mobile dan tabel lengkap di desktop.
- Armada sudah memakai card list compact di mobile dan tabel lengkap di desktop.
- Jadwal sudah memakai pola card per hari/per rute, jadi tidak perlu tabel mobile tambahan.
- Segment sudah memakai card list compact di mobile dan tabel lengkap di desktop.
- Laporan Booking, Carter, dan Bagasi sudah memakai card list mobile dengan pagination ringkas yang sama.
- Validasi otomatis sudah dilakukan dengan `npm.cmd run types:check`, `npm.cmd run build`, dan `git diff --check`.
- Sisa QA manual: cek visual di lebar 360px, 390px, dan 430px pada browser/perangkat nyata.

## Pola UI Yang Dipakai

- Desktop tetap memakai tabel lengkap.
- Mobile memakai card list compact.
- Search dan filter mobile default tersembunyi, lalu bisa dibuka dengan tombol Filter.
- Aksi row seperti Edit, Hapus, Detail, Salin, dan Print masuk ke menu titik tiga.
- Pagination mobile dibuat ringkas: `Prev`, `page / last_page`, `Next`.
- Data angka besar diringkas menjadi 2-4 metrik utama di card mobile.

## Menu Yang Sudah Mulai Dibuat Mobile Friendly

- Customer Reguler.
- Customer Bagasi.
- Customer Carter.
- Pengaturan Pool.
- Pengaturan Users.
- Logs Aktivitas.

## Tahap Berikutnya

### 1. Rute Induk

Masalah saat ini:

- Tabel Rute Induk sangat lebar karena memuat formula bulanan revenue, BOP, margin, target, dan status.
- Di mobile, informasi finansial sulit dibaca jika tetap dipaksa sebagai tabel.

Konsep mobile:

- Card utama menampilkan `Nama Rute`, `Origin`, `Destination`, dan `Status`.
- Metrik utama ditampilkan 2 baris: `Revenue`, `BOP`, `Net Margin`, `Achievement`.
- Detail breakdown seperti Charter, Keberangkatan, Bagasi, Fixed Cost bisa masuk expand/detail.
- Aksi Edit dan Hapus masuk menu titik tiga.

Prioritas field mobile:

- Nama rute.
- Origin dan destination.
- Total revenue.
- Total BOP.
- Net margin.
- Target achievement.
- Status.

### 2. Jadwal

Masalah saat ini:

- Jadwal punya kombinasi rute, hari, jam, unit, dan armada yang cepat melebar.
- Mobile perlu fokus pada jadwal aktif, bukan semua detail teknis sekaligus.

Konsep mobile:

- Group per rute atau per hari.
- Card menampilkan `Rute`, `Hari`, `Jam`, `Unit`, dan `Armada`.
- Jika ada banyak unit dalam satu jadwal, tampilkan ringkasan `3 unit` lalu detail bisa dibuka.
- Aksi Edit, Hapus, dan kelola unit masuk menu titik tiga.

Prioritas field mobile:

- Rute.
- Hari.
- Jam.
- Unit/nopol.
- Status aktif bila ada.

### 3. Driver

Masalah saat ini:

- Tabel driver memuat banyak metrik bulanan dan menjadi berat di mobile.
- Data identitas dan performa perlu dipisahkan agar tidak terlihat padat.

Konsep mobile:

- Header card menampilkan `Nama Driver`, `Nomor HP`, dan `Nopol`.
- Metrik compact: `Revenue`, `BOP`, `Net Margin`, `Achievement`.
- Detail breakdown Charter/Keberangkatan/Bagasi bisa masuk expand/detail.
- Aksi Edit/Hapus masuk menu titik tiga.

Prioritas field mobile:

- Nama driver.
- Nomor HP.
- Nopol atau armada terpasang.
- Total revenue.
- Net margin.
- Target achievement.

### 4. Armada

Masalah saat ini:

- Armada memiliki data teknis kendaraan, target, fixed cost, dan performa bulanan.
- Tabel besar masih cocok untuk desktop, tapi terlalu berat untuk mobile.

Konsep mobile:

- Card menampilkan `Nopol`, `Merk`, `Kategori/Layout`, dan `Status`.
- Metrik ringkas: `Revenue`, `BOP`, `Fixed Cost`, `Achievement`.
- Detail teknis seperti nomor rangka, warna, tahun, GPS masuk area detail.
- Aksi Edit, Hapus, dan detail performa masuk menu titik tiga.

Prioritas field mobile:

- Nopol.
- Merk.
- Kategori/layout.
- Status.
- Total revenue.
- Fixed cost.
- Achievement.

### 5. Segment

Masalah saat ini:

- Segment biasanya lebih sederhana, tapi tetap bisa terasa lebar jika route, origin, destination, dan harga ditampilkan bersamaan.

Konsep mobile:

- Card menampilkan `Nama Segment`, `Rute Induk`, dan `Harga`.
- Origin dan destination ditampilkan sebagai arah perjalanan.
- Aksi Edit/Hapus masuk menu titik tiga.

Prioritas field mobile:

- Nama segment.
- Rute induk.
- Origin dan destination.
- Harga.

### 6. Laporan

Masalah saat ini:

- Tabel laporan booking, carter, dan bagasi memuat banyak kolom.
- Untuk laporan, sebagian tabel memang masih dibutuhkan, tapi mobile perlu mode ringkas.

Konsep mobile:

- Laporan mobile memakai card list per transaksi.
- Summary laporan tetap tampil di atas sebagai ringkasan total.
- Filter periode dan pool tetap bisa dibuka/tutup.
- Detail transaksi bisa dibuka dari card.
- Kolom angka utama tetap ditampilkan jelas: total pembayaran, status, tanggal.

Prioritas field laporan booking:

- Tanggal dan jam.
- Nama penumpang.
- Rute.
- Unit dan seat.
- Pembayaran/status.
- Total.

Prioritas field laporan carter:

- Tanggal mulai.
- Nama customer.
- Rute carter.
- Driver/armada.
- Status bayar.
- Total.

Prioritas field laporan bagasi:

- Tanggal.
- Pengirim dan penerima.
- Rute.
- Status.
- Total.

## Urutan Eksekusi Rekomendasi

1. Rute Induk.
2. Driver.
3. Armada.
4. Jadwal.
5. Segment.
6. Laporan.

Alasan urutan:

- Rute, Driver, dan Armada paling sering menampilkan metrik bulanan yang lebar.
- Jadwal dan Segment lebih sederhana setelah pola card terbentuk.
- Laporan sebaiknya dikerjakan terakhir karena formatnya berbeda dan lebih dekat ke kebutuhan export/detail.

## Validasi Yang Perlu Dilakukan

- Jalankan `npm.cmd run build`.
- Jalankan `git diff --check`.
- Cek tampilan mobile pada lebar 360px, 390px, dan 430px.
- Pastikan desktop tetap memakai tabel lengkap.
- Pastikan aksi Edit/Hapus/Detail tetap bekerja dari card mobile.
- Pastikan filter mobile tidak menutup data utama.
