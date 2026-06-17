Data Keberangkatan OptiBus saat ini, tampilannya sudah cukup bersih dengan gaya modern (light mode dan rounded corners). Namun, untuk aplikasi operasional (back-office/workspace) yang padat informasi, ada beberapa aspek UI/UX yang bisa ditingkatkan agar lebih efisien, mengurangi lelah mata, dan mempercepat kerja admin.

Berikut adalah beberapa rekomendasi improvement UI/UX tampilan desktop menu data keberangkatan yang bisa diterapkan:

1. Optimalisasi Layout & Kepadatan Data (Density)
Aplikasi operasional biasanya membutuhkan efisiensi ruang agar pengguna tidak perlu terlalu banyak melakukan scrolling.

Ubah Grid Card Menjadi List/Table View (Alternatif): Tampilan berbentuk card besar seperti sekarang bagus untuk mobile, tetapi di desktop memakan banyak ruang vertikal. Pertimbangkan untuk menyediakan toggle perpindahan antara Grid View dan Table View. Data seperti Plat Nomor, Driver, dan BOP akan jauh lebih mudah dipindai (scannable) jika berbentuk baris tabel.

Grup Berdasarkan Tanggal yang Lebih Jelas: Saat ini, tanggal ditulis di setiap card individu (misal ada 3 card untuk tanggal 12 Jun). Sebaiknya buat satu Header Tanggal yang besar, lalu di bawahnya terdapat daftar unit/jadwal yang berangkat di hari tersebut. Ini akan memotong redundancy teks tanggal.

2. Peningkatan Keterbacaan Status (Label T, A, C, L, RF, BL)
Bagian bawah setiap card memiliki indikator seperti T 3, A 3, C 0, dst. Bagi pengguna baru atau dalam situasi panik, singkatan satu huruf ini bisa membingungkan.

Gunakan Tooltip atau Icon: Jika T berarti Total, A berarti Anak/Dewasa (atau manifest tertentu), berikan visual pendukung atau minimal tooltip saat kursor diarahkan ke sana.

Warna Berbasis Urgensi: Saat ini semua label menggunakan warna soft outline yang mirip (pink, hijau, biru). Berikan kontras warna yang lebih tegas untuk status kritis. Misalnya, jika BL artinya "Belum Lunas", warnanya bisa dibuat agak kemerahan/oranye tua agar perhatian admin langsung tertuju ke sana untuk melakukan penagihan.

3. Penyempurnaan Komponen Kontrol & Filter
Tombol "Tambah Jadwal": Posisi tombol Primary Action ini diletakkan sejajar dengan tab "Aktif/History", namun ukurannya cukup besar di kiri. Jika ini adalah aksi yang sangat sering ditekan, posisinya sudah oke. Tapi jika jarang, tombol ini bisa dipindahkan ke pojok kanan atas halaman agar area filter di bawahnya bisa naik dan lebih ringkas.

Placeholder Filter yang Lebih Spesifik: Input pencarian bertuliskan "Cari kode, rute, nama, telepon, jam, unit". Teks ini terlalu panjang untuk sebuah placeholder. Lebih baik pecah menjadi filter spesifik (misal: dropdown khusus Rute, dropdown khusus Jam) atau gunakan ikon kaca pembesar dengan placeholder ringkas: "Cari keberangkatan..."

Sticky Filter Header: Pastikan area pencarian dan filter tetap melayang (sticky) di atas saat admin melakukan scroll ke bawah, agar mereka tidak perlu kembali ke atas hanya untuk mengganti tanggal atau rute.

4. Visual Hierarchy & Penegasan Elemen Kunci
Kontras Teks Jam: Angka jam (seperti 16:00, 09:00) adalah informasi paling krusial setelah rute. Saat ini ukurannya sudah besar, namun penempatannya di sebelah kanan agak terpisah dari informasi utama. Menyatukannya dalam satu baris hierarki dengan Kode Keberangkatan atau Rute akan membuatnya lebih natural dibaca dari kiri ke kanan.

Status Driver & Nopol Kosong: Untuk jadwal yang belum memiliki Driver atau Nopol (ditandai dengan -), berikan gaya teks italic dengan warna abu-abu pudar atau label "Belum Ditugaskan" berwarna kuning peringatan. Tanda minus (-) sekilas terlihat seperti data yang eror atau terpotong.

Contoh Ilustrasi Perubahan Komponen (Mockup Konsep)
Jika diimplementasikan dalam struktur kode atau desain baru, transformasi card keberangkatan bisa menjadi lebih padat seperti ini:

[ 12 Juni 2026 ] (Sebagai Header Grup)

Jam	Kode / Rute	Driver & Armada	Info Manifest / Pembayaran	Aksi
09:00	
DEP-260612-0900


PINRANG â†’ MAKASSAR

NAHAR


<small style="color:gray">DD 7151 RZ (Unit 1)</small>

<span style="color:green">â— 3 Kursi</span> Â· <span style="color:red">2 Belum Lunas</span>


<small>BOP: Rp 245.000</small>

[Â·Â·Â·]
17:00	
DEP-260612-1700


PINRANG â†’ MAKASSAR

SARDI


<small style="color:gray">B 7214 SDB (Unit 1)</small>

<span style="color:green">â— 6 Kursi</span> Â· <span style="color:green">Lunas</span>


<small>BOP: Rp 270.000</small>

[Â·Â·Â·]
Dengan mengubahnya menjadi berbasis tabel/list berkelompok seperti di atas, admin bisa melihat lebih banyak jadwal sekaligus dalam satu layar tanpa harus banyak melakukan scrolling.

Apakah ada bagian spesifik dari halaman ini (seperti alur Booking Console atau filter tanggal) yang ingin Anda bedah lebih dalam UX-nya?

Setelah melihat halaman Detail Keberangkatan (DEP-260611-1600-U01-891) ini, secara UI tampilannya sudah menggunakan dashboard design yang modern dan bersih. Namun, sebagai halaman kerja operasional (workspace), halaman ini masih bisa dioptimalkan agar admin dapat bekerja lebih cepat, minim klik, dan informasi penting langsung terlihat tanpa banyak scrolling.

Berikut adalah rekomendasi improvement UI/UX tampilan desktop khusus untuk halaman detail dimenu data keberangkatan :

1. Tata Letak (Layout) & Efisiensi Ruang
Saat ini, layout menggunakan satu kolom besar ke bawah (single column). Ini membuat halaman menjadi sangat panjang dan memaksa admin banyak melakukan scroll.

Gunakan Split-Screen Layout (2 Kolom):

Kolom Kiri (Lebih Sempit - 35%): Tempatkan Ringkasan Keberangkatan, Mapping Keberangkatan (Driver/Nopol), dan Info Perjalanan.

Kolom Kanan (Lebih Lebar - 65%): Tempatkan Daftar Penumpang / Manifest (karena bagian ini membutuhkan ruang horizontal yang luas untuk tabel).

Efek Positif: Admin bisa melihat status manifest penumpang sekaligus mengubah nama driver di saat yang bersamaan tanpa perlu scroll naik-turun.

2. Optimasi "Ringkasan Keberangkatan" (Metrics Card)
Bagian ringkasan saat ini memakan ruang vertikal yang cukup besar untuk informasi yang sebenarnya bisa dibuat lebih ringkas.

Buat Menjadi 1 Baris Horizontal: Ubah susunan kotak Total, Aktif, Cancel, Lunas, Refund, dan Belum Lunas menjadi satu baris horizontal yang ramping di bagian atas halaman.

Gunakan Fitur "Badges" yang Lebih Kontras: * Untuk Belum Lunas (Rp 120.000), gunakan warna teks oranye tua/merah dengan background soft yellow/red agar langsung memicu urgensi admin untuk menagih.

Saat ini warna tulisan angka "1" pada Belum Lunas berwarna hijau, yang secara psikologis UX bisa membingungkan (karena hijau biasanya diidentikkan dengan aman/lunas).

3. Komponen "Mapping Keberangkatan" (Driver & Armada)
Bagian ini sangat krusial karena sering diubah di menit-menit terakhir sebelum bus berangkat.

Tombol "Simpan" yang Terisolasi: Tombol "Simpan" saat ini berada di pojok kanan bawah form input. Jika dipindahkan ke dalam layout 2 kolom, pastikan posisinya menyatu dengan input Driver & Nopol.

Status "Memuat data...": Jika proses loading data driver/armada sering memakan waktu, berikan animasi skeleton loading yang halus daripada sekadar teks abu-abu agar aplikasi terasa lebih responsif.

4. Hierarchy Tombol Aksi Utama (Action Buttons)
Di bagian atas kanan, terdapat deretan tombol: Print Manifest, Lunaskan Semua, Copy Data, dan Kembali.

Bedakan Bobot Visual Tombol: Saat ini tombol Lunaskan Semua menggunakan warna biru pekat (Primary Action). Ini sudah bagus jika memang itu fitur yang paling sering digunakan. Namun, pastikan ada konfirmasi pop-up (modal) setelah tombol ini ditekan untuk mencegah salah klik yang tidak sengaja melunaskan seluruh manifes.

Ikonografi: Tambahkan ikon kecil di sebelah teks Copy Data (misal ikon double-document) dan Kembali (ikon panah kiri) untuk mempercepat pengenalan fungsi tombol secara visual.

Contoh Ilustrasi Perubahan Struktur Halaman
Jika diubah menjadi layout yang lebih efisien, strukturnya akan terlihat seperti ini:

+-----------------------------------------------------------------------------------------+
| [Kembali]  RUTE: PINRANG â†’ MAKASSAR (Kam, 11 Jun - 16:00)           [Print] [Copy] [...] |
+-----------------------------------------------------------------------------------------+
| Ringkasan: Total: 2 | Aktif: 1 | Cancel: 1 | Lunas: 1 | Belum Lunas: 1 (Rp 120.000)     |
+-----------------------------------------------------------------------------------------+
| (KOLOM KIRI: 35%)                         | (KOLOM KANAN: 65%)                          |
|                                           |                                             |
| > MAPPING KEBERANGKATAN                   | > DAFTAR PENUMPANG / MANIFEST               |
|   Driver: [ Dropdown Pilih Driver ]       |   +---------+-----------+----------------+  |
|   Nopol:  [ Dropdown Pilih Armada ]       |   | Seat    | Nama      | Status         |  |
|   [ Tombol Simpan ]                       |   +---------+-----------+----------------+  |
|                                           |   | 2       | Meta      | Belum Lunas    |  |
| > INFO PERJALANAN                         |   +---------+-----------+----------------+  |
|   Kode: DEP-260611-1600...                |                                             |
|   BOP: Rp 270.000                         |                                             |
+-----------------------------------------------------------------------------------------+
