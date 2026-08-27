# 📋 PANDUAN PENGGUNAAN APLIKASI
## Sistem Point of Sale (POS) — Toko Ade Fitri

> **Alamat Toko:** Pasar Karangampel Blok D4-D6 | **WA:** 082315580021
> **URL Aplikasi:** http://toko-ade-fitri.test | **Port Lokal:** http://127.0.0.1:8000

---

## DAFTAR ISI

1. [Persiapan Awal — Menjalankan Laragon](#1-persiapan-awal--menjalankan-laragon)
2. [Login ke Sistem](#2-login-ke-sistem)
3. [Dashboard (Beranda Utama)](#3-dashboard-beranda-utama)
4. [Kasir POS — Transaksi Penjualan](#4-kasir-pos--transaksi-penjualan)
5. [Riwayat Transaksi](#5-riwayat-transaksi)
6. [Manajemen Produk Toko](#6-manajemen-produk-toko)
7. [Kategori Produk](#7-kategori-produk)
8. [Manajemen Supplier](#8-manajemen-supplier)
9. [Tagihan Supplier](#9-tagihan-supplier)
10. [Manajemen Pelanggan](#10-manajemen-pelanggan)
11. [Kelola Stok](#11-kelola-stok)
12. [Laporan Toko](#12-laporan-toko)
13. [Kelola Staff / Pengguna](#13-kelola-staff--pengguna)
14. [Pengaturan Akun Pribadi](#14-pengaturan-akun-pribadi)
15. [Panduan Singkat untuk Karyawan Kasir](#15-panduan-singkat-untuk-karyawan-kasir)
16. [Troubleshooting — Masalah Umum](#16-troubleshooting--masalah-umum)

---

## 1. Persiapan Awal — Menjalankan Laragon

### Langkah 1 — Buka Laragon

1. Cari ikon **Laragon** di Desktop atau System Tray (pojok kanan bawah taskbar)
2. Klik dua kali ikon Laragon untuk membuka panel kontrol
3. Klik tombol **"Start All"** di panel Laragon
   - Apache / Nginx akan berjalan (lampu hijau)
   - MySQL akan berjalan (lampu hijau)
4. Tunggu hingga kedua layanan aktif (beberapa detik)

> **Tip:** Pastikan port **80** dan **3306** tidak diblokir aplikasi lain seperti XAMPP atau program lain yang menggunakan port yang sama.

### Langkah 2 — Buka Browser

1. Buka browser (Google Chrome direkomendasikan)
2. Ketikkan salah satu URL berikut di address bar:
   - `http://toko-ade-fitri.test` *(jika domain Laragon sudah dikonfigurasi)*
   - `http://127.0.0.1:8000` *(jika menggunakan dev server Laravel)*
3. Tekan **Enter** — halaman login aplikasi akan muncul

---

## 2. Login ke Sistem

### Halaman Login

Setelah membuka URL, Anda akan diarahkan ke halaman **Login**.

| Field | Isi |
|-------|-----|
| Email | Alamat email akun Anda |
| Password | Password akun Anda |

**Langkah login:**
1. Masukkan **Email** akun yang sudah terdaftar
2. Masukkan **Password**
3. Klik tombol **"Masuk"** / **"Login"**
4. Sistem akan mengarahkan ke **Dashboard** secara otomatis

### Hak Akses / Role

| Role | Akses Menu |
|------|-----------|
| **Admin** | Semua fitur termasuk Kelola Staff |
| **Owner** | Semua fitur termasuk Kelola Staff |
| **Karyawan** | Dashboard, POS Kasir, Produk, Stok, Transaksi, Laporan, Pelanggan (tanpa Kelola Staff) |

---

## 3. Dashboard (Beranda Utama)

**Menu:** Klik **"Dashboard"** di sidebar kiri

Dashboard adalah halaman ringkasan performa toko secara real-time.

### Yang Tersedia di Dashboard:

#### Kartu Statistik Hari Ini
- **Omzet Hari Ini** — Total penjualan dalam Rupiah
- **Total Transaksi** — Jumlah transaksi yang sudah selesai
- **Rata-rata Transaksi** — Rata-rata nilai per transaksi
- **Pelanggan Baru** — Jumlah pelanggan baru yang terdaftar

#### Grafik Tren Penjualan
- Menampilkan tren omzet 7 hari terakhir dalam bentuk grafik batang
- Warna biru menunjukkan hari biasa, warna lebih terang menunjukkan hari ini

#### Produk Terlaris
- Daftar 5 produk dengan penjualan tertinggi berdasarkan jumlah unit terjual dan omzet
- Dilengkapi kategori produk dan nama produk

#### Stok Menipis
- Produk yang stoknya mendekati atau di bawah batas minimum (min_stock)
- Klik tombol **"Kelola Stok"** untuk langsung menambah stok

#### Transaksi Terakhir
- Daftar transaksi terbaru dengan nomor invoice, pelanggan, dan total pembayaran

---

## 4. Kasir POS — Transaksi Penjualan

**Menu:** Klik **"Kasir POS"** di sidebar kiri

Ini adalah fitur utama untuk melayani pelanggan dan mencatat transaksi penjualan.

### Tampilan Kasir

Layar dibagi menjadi dua bagian:
- **Kiri:** Katalog produk (daftar semua produk aktif)
- **Kanan:** Keranjang belanja & ringkasan pembayaran

---

### Langkah Melakukan Transaksi

#### A. Pilih Pelanggan (Opsional)
1. Di panel kanan atas, klik dropdown **"Pelanggan"**
2. Pilih nama pelanggan (jika terdaftar), atau biarkan **"-- Pelanggan Umum (Eceran) --"**
   - Pelanggan tipe **Eceran** → Harga eceran yang berlaku
   - Pelanggan tipe **Grosir** → Harga grosir yang berlaku

#### B. Cari & Tambah Produk ke Keranjang
1. Gunakan **kotak pencarian** di kiri atas untuk mencari produk berdasarkan:
   - Nama produk
   - SKU produk
   - Barcode produk
2. Atau filter berdasarkan **Kategori** dengan mengklik tombol kategori
3. **Klik produk** untuk menambahkannya ke keranjang
   - Produk dengan stok **HABIS** tidak dapat ditambahkan
4. Produk akan muncul di panel keranjang sebelah kanan

#### C. Kelola Item di Keranjang
Setiap item di keranjang memiliki kontrol:

| Kontrol | Fungsi |
|---------|--------|
| **+ / -** | Tambah atau kurangi jumlah (qty) |
| **Badge Eceran/Grosir** | Klik untuk beralih tipe harga |
| **Hapus** | Hapus item dari keranjang |

#### D. Diskon dan Pajak (Opsional)
Di bagian bawah keranjang:
- **Potongan / Diskon (Rp):** Isi nominal diskon dalam Rupiah
- **Pajak PPN (Rp):** Isi nominal pajak dalam Rupiah

#### E. Proses Pembayaran
1. Periksa **TOTAL BAYAR** yang tertera
2. Klik tombol **"Bayar Sekarang"** (tombol biru besar)
3. Modal **"Selesaikan Pembayaran"** akan muncul

**Dalam Modal Pembayaran:**

| Metode | Langkah |
|--------|---------|
| **Tunai / Cash** | Masukkan nominal uang diterima, pilih nominal cepat atau ketik manual, sistem hitung kembalian otomatis |
| **QRIS** | Perlihatkan QR code ke pelanggan, setelah pelanggan bayar centang "Verifikasi Pembayaran" |

4. Klik **"Cetak & Selesaikan"**
5. Sistem memproses transaksi dan menampilkan **struk invoice**

#### F. Struk Invoice
Setelah transaksi berhasil, modal struk muncul dengan 2 pilihan:
- **"Selesai & Buka Kasir"** — Tutup struk, langsung siap transaksi berikutnya
- **"Cetak Struk & Selesai"** — Cetak struk ke printer thermal (80mm), lalu otomatis kembali ke kasir

> **Reset Otomatis:** Setelah transaksi selesai, keranjang, diskon, pajak, pelanggan, dan pencarian otomatis dikosongkan — siap untuk transaksi berikutnya.

---

## 5. Riwayat Transaksi

**Menu:** Klik **"Riwayat Transaksi"** di sidebar kiri

Halaman ini menampilkan semua invoice transaksi yang pernah terjadi.

### Filter & Pencarian
- **Cari nomor invoice** — Ketik nomor invoice lalu tekan Enter
- **Filter Metode Bayar** — Pilih: Semua / Tunai / QRIS / Transfer Bank / Debit
- **Filter Tanggal** — Pilih tanggal tertentu untuk menyaring transaksi

### Tabel Transaksi
Kolom yang tersedia:

| Kolom | Keterangan |
|-------|-----------|
| No. Invoice | Kode unik transaksi (format: INV-YYYYMMDD-XXXX) |
| Tanggal & Waktu | Waktu transaksi dibuat |
| Pelanggan | Nama pelanggan atau "Pelanggan Umum" |
| Kasir Staff | Nama karyawan yang memproses |
| Metode Bayar | CASH / QRIS |
| Status | SELESAI (hijau) / DIBATALKAN (merah) |
| Grand Total | Total pembayaran |

### Aksi per Transaksi

| Tombol | Fungsi |
|--------|--------|
| Lihat (mata) | Buka detail invoice & cetak ulang struk |
| Edit (pensil) | Ubah pelanggan, metode bayar, atau status transaksi |
| Hapus (tong sampah) | Hapus transaksi (stok otomatis dikembalikan) |

### Cetak Ulang Struk
1. Klik tombol **Lihat** pada transaksi yang ingin dicetak
2. Modal invoice akan terbuka dengan tampilan struk
3. Klik **"Cetak Struk"** untuk mencetak

---

## 6. Manajemen Produk Toko

**Menu:** Klik **"Produk Toko"** di sidebar kiri

### Tambah Produk Baru
1. Klik tombol **"+ Tambah Produk"**
2. Isi form data produk:

| Field | Keterangan |
|-------|-----------|
| Nama Produk* | Nama lengkap produk |
| SKU* | Kode unik produk (Stock Keeping Unit) |
| Barcode | Kode barcode produk (opsional) |
| Kategori* | Pilih dari daftar kategori |
| Supplier | Pilih supplier produk (opsional, bisa multi-supplier) |
| Harga Beli (HPP)* | Harga pokok pembelian dari supplier |
| Harga Jual Eceran* | Harga jual untuk pelanggan eceran |
| Harga Jual Grosir* | Harga jual untuk pelanggan grosir |
| Stok Awal* | Jumlah stok saat ini |
| Minimum Stok | Batas stok minimum sebelum alert tampil di dashboard |
| Satuan | pcs / kg / liter / lusin / dll |
| Catatan | Keterangan tambahan produk |
| Status Aktif | Aktif = muncul di kasir, Nonaktif = tersembunyi dari kasir |

3. Klik **"Simpan Produk"**

### Filter Produk
- **Cari** nama, SKU, atau barcode
- **Filter Kategori** — Pilih dari dropdown kategori
- **Filter Stok Menipis** — Aktifkan untuk melihat produk dengan stok rendah

### Edit & Hapus Produk
- Klik **Edit** untuk mengubah data produk
- Klik **Hapus** untuk menghapus produk *(hanya bisa dihapus jika tidak ada riwayat transaksi)*

---

## 7. Kategori Produk

**Menu:** Klik **"Kategori"** di sidebar kiri

Digunakan untuk mengelompokkan produk berdasarkan jenis.

### Tambah Kategori Baru
1. Klik **"+ Tambah Kategori"**
2. Isi **Nama Kategori** (contoh: Plastik, Bahan Kue, Minuman, dll)
3. Klik **"Simpan"**

### Aturan Penghapusan
Kategori **tidak bisa dihapus** jika masih ada produk yang menggunakan kategori tersebut. Pindahkan atau hapus produk terkait terlebih dahulu.

---

## 8. Manajemen Supplier

**Menu:** Klik **"Supplier"** di sidebar kiri

Menyimpan data pemasok/supplier produk.

### Tambah Supplier Baru
1. Klik **"+ Tambah Supplier"**
2. Isi data:

| Field | Keterangan |
|-------|-----------|
| Nama Supplier* | Nama toko/perusahaan supplier |
| Kontak / PIC | Nama orang yang bisa dihubungi |
| Nomor Telepon | No. telepon supplier |
| Alamat | Alamat lengkap supplier |
| Catatan | Keterangan tambahan |

3. Klik **"Simpan"**

### Edit & Hapus Supplier
- Klik **Edit** untuk mengubah data
- Klik **Hapus** untuk menghapus *(tidak bisa dihapus jika ada tagihan/invoice aktif)*

---

## 9. Tagihan Supplier

**Menu:** Klik **"Tagihan Supplier"** di sidebar kiri

Fitur untuk mencatat pembelian barang dari supplier dan melacak status pembayarannya.

### Ringkasan Metrik (Kartu Atas)
- **Total Tagihan** — Jumlah total semua tagihan
- **Sudah Dibayar** — Total yang sudah lunas
- **Belum Dibayar** — Sisa hutang
- **Jatuh Tempo Dekat** — Jumlah tagihan yang akan segera jatuh tempo

### Tambah Tagihan Baru
1. Klik **"+ Buat Tagihan Baru"**
2. Isi informasi tagihan:
   - **Supplier** — Pilih dari daftar supplier
   - **Nomor Invoice Supplier** — Nomor faktur dari supplier
   - **Tanggal Jatuh Tempo** — Kapan tagihan harus dilunasi
   - **Item yang Dibeli** — Tambah item satu per satu:
     - Pilih produk atau isi nama manual
     - Isi jumlah dan harga per unit
     - Subtotal dihitung otomatis
3. Klik **"Simpan Tagihan"**

### Status Tagihan

| Status | Keterangan |
|--------|-----------|
| Belum Dibayar (merah) | Belum ada pembayaran sama sekali |
| Sebagian Dibayar (kuning) | Sudah bayar sebagian, masih ada sisa |
| Lunas (hijau) | Sudah dibayar penuh |

### Bayar Tagihan (Bisa Cicilan)
1. Klik tombol bayar pada tagihan yang ingin dibayar
2. Isi **Jumlah Pembayaran** (bisa sebagian/cicilan)
3. Pilih **Metode Pembayaran**
4. Isi catatan jika perlu
5. Klik **"Simpan Pembayaran"**
6. Status tagihan otomatis berubah sesuai sisa pembayaran

### Lihat Riwayat Pembayaran
Klik **Detail** untuk melihat semua cicilan pembayaran yang sudah dilakukan.

---

## 10. Manajemen Pelanggan

**Menu:** Klik **"Pelanggan"** di sidebar kiri

Menyimpan data pelanggan tetap toko (eceran & grosir).

### Tambah Pelanggan Baru
1. Klik **"+ Tambah Pelanggan"**
2. Isi data:

| Field | Keterangan |
|-------|-----------|
| Nama Pelanggan* | Nama lengkap pelanggan |
| Nomor Telepon | HP/WA pelanggan |
| Alamat | Alamat pelanggan |
| Tipe* | **Eceran** (harga eceran) atau **Grosir** (harga grosir) |

3. Klik **"Simpan"**

### Sistem Poin Pelanggan
- Setiap transaksi yang mencantumkan nama pelanggan akan mendapat **poin otomatis**
- Formula: **1 poin = setiap Rp 10.000** grand total transaksi
- Poin dapat dilihat di kolom **"Poin"** pada tabel pelanggan
- Poin dapat dijadikan referensi program loyalitas toko

### Filter Pelanggan
- **Cari** berdasarkan nama
- **Filter Tipe**: Semua / Eceran / Grosir

---

## 11. Kelola Stok

**Menu:** Klik **"Kelola Stok"** di sidebar kiri

Fitur untuk memantau dan menyesuaikan pergerakan stok produk secara manual.

### Jenis Pergerakan Stok

| Tipe | Keterangan | Efek |
|------|-----------|------|
| **Masuk (in)** | Terima barang dari supplier | Stok bertambah |
| **Keluar (out)** | Barang hilang/rusak/retur | Stok berkurang |
| **Penyesuaian (adjustment)** | Koreksi stok setelah stok opname | Stok disesuaikan |

> **Catatan:** Penjualan melalui kasir POS akan otomatis mencatat stok keluar tanpa perlu input manual di sini.

### Tambah Pergerakan Stok
1. Klik **"+ Tambah Pergerakan Stok"**
2. Isi form:
   - **Produk** — Pilih produk dari dropdown
   - **Tipe** — Masuk / Keluar / Penyesuaian
   - **Jumlah** — Berapa unit yang bergerak
   - **Alasan / Keterangan** — Misal: "Restock dari Supplier ABC", "Barang rusak", "Stok opname"
   - **No. Referensi** — Nomor faktur atau dokumen terkait (opsional)
3. Klik **"Simpan"**

### Filter Riwayat Pergerakan
- Filter berdasarkan **Produk** tertentu
- Filter berdasarkan **Tipe** (Masuk/Keluar/Penyesuaian)

---

## 12. Laporan Toko

**Menu:** Klik **"Laporan Toko"** di sidebar kiri

Halaman analisis dan laporan keuangan toko.

### Filter Periode Laporan

| Pilihan | Keterangan |
|---------|-----------|
| **7 Hari Terakhir** | Laporan seminggu terakhir |
| **30 Hari Terakhir** | Laporan sebulan terakhir |
| **Bulan Ini** | Laporan bulan berjalan |
| **Tahun Ini** | Laporan setahun berjalan |
| **Custom** | Tentukan tanggal awal dan akhir sendiri |

Setelah memilih periode, klik **"Terapkan Filter"**

### Kartu Ringkasan Keuangan
- **Total Pendapatan** — Jumlah omzet dalam periode
- **Jumlah Transaksi** — Berapa kali transaksi terjadi
- **Estimasi Laba Kotor** — Pendapatan dikurangi HPP (harga pokok)
- **Periode Laporan** — Rentang tanggal yang sedang dilihat

### Tabel Data Harian
Tabel omzet dan jumlah transaksi per hari dalam periode yang dipilih.

### Breakdown Metode Pembayaran
Persentase penggunaan setiap metode bayar (Tunai vs QRIS) dalam periode laporan.

### Top Produk Terlaris
Daftar produk paling banyak terjual dalam periode, beserta total unit dan omzetnya.

### Export CSV
1. Atur filter periode yang diinginkan
2. Klik tombol **"Export CSV"**
3. File Excel/CSV akan otomatis terunduh ke komputer
4. File berisi data transaksi detail yang bisa dibuka di Excel/Google Sheets

---

## 13. Kelola Staff / Pengguna

**Menu:** Klik **"Kelola Staff"** di sidebar kiri

> **Akses Terbatas:** Menu ini hanya tersedia untuk role **Admin** dan **Owner**. Karyawan tidak dapat mengakses menu ini.

### Daftar Staff
Tabel menampilkan semua akun pengguna dengan informasi:
- Nama Lengkap
- Alamat Email
- Hak Akses / Role
- Tanggal Terdaftar

### Tambah Staff Baru
1. Klik **"+ Tambah Staff Baru"**
2. Isi data:

| Field | Keterangan |
|-------|-----------|
| Nama Lengkap* | Nama lengkap karyawan |
| Email* | Alamat email untuk login |
| Password* | Password awal (minimal 8 karakter) |
| Role* | Karyawan / Admin / Owner |

3. Klik **"Simpan"**
4. Informasikan email dan password ke karyawan bersangkutan

### Edit Data Staff
1. Klik **Edit** pada baris staff
2. Ubah data yang perlu diubah
3. Kosongkan kolom **Password** jika tidak ingin mengubah password
4. Klik **"Simpan Perubahan"**

### Hapus Akun Staff
1. Klik **Hapus** pada baris staff
2. Konfirmasi penghapusan
3. Akun staff dihapus dari sistem

---

## 14. Pengaturan Akun Pribadi

**Akses:** Klik nama/avatar Anda di pojok kiri bawah sidebar

### Pengaturan Profil
- Ubah **Nama** dan **Email** akun Anda
- Simpan perubahan dengan klik **"Simpan"**

### Ubah Password
1. Masukkan password lama
2. Masukkan password baru
3. Konfirmasi password baru
4. Klik **"Ubah Password"**

### Pengaturan Tampilan (Appearance)
- Pilih **Mode Gelap (Dark Mode)** atau **Mode Terang (Light Mode)**
- Perubahan langsung diterapkan

---

## 15. Panduan Singkat untuk Karyawan Kasir

Panduan ringkas untuk karyawan yang hanya bertugas di kasir:

### Alur Kerja Harian Kasir

**Memulai Kerja:**
1. Buka Laragon → Start All
2. Buka Browser → `http://toko-ade-fitri.test`
3. Login dengan akun karyawan
4. Cek Dashboard → perhatikan stok menipis

**Untuk Setiap Transaksi:**
1. Klik "Kasir POS" di menu kiri
2. (Opsional) Pilih pelanggan dari dropdown
3. Cari produk → klik untuk masuk keranjang
4. Sesuaikan jumlah/qty jika perlu
5. Isi diskon jika ada
6. Klik **"Bayar Sekarang"**
7. Pilih metode bayar (Tunai / QRIS)
8. Proses pembayaran → Klik **"Cetak & Selesaikan"**
9. Cetak struk jika diminta pelanggan
10. Klik **"Selesai & Buka Kasir"** → Siap transaksi berikutnya

**Jika Ada Masalah Stok:**
- Lapor ke Admin/Owner untuk input stok masuk
- Admin/Owner buka menu "Kelola Stok" → Tambah stok

---

## 16. Troubleshooting — Masalah Umum

### Halaman Tidak Bisa Dibuka
- Pastikan Laragon sudah dijalankan (tombol Start All aktif)
- Coba URL alternatif: `http://127.0.0.1:8000`
- Coba buka melalui Laragon (klik kanan tray icon → "localhost")

### Login Gagal
- Pastikan email dan password benar (perhatikan huruf besar/kecil)
- Hubungi Admin/Owner untuk reset password

### Produk Tidak Muncul di Kasir
- Pastikan produk statusnya **Aktif** di menu Produk Toko
- Pastikan stok produk **lebih dari 0**

### Struk Tidak Bisa Dicetak
- Pastikan printer thermal sudah terhubung dan menyala
- Pastikan browser mengizinkan akses printer (klik Izinkan saat dialog muncul)
- Ukuran kertas printer harus **80mm**

### Stok Tidak Bertambah Setelah Input
- Refresh halaman (tekan `F5`)
- Pastikan input stok sudah disimpan (ada notifikasi sukses)

### Tampilan Tidak Berubah Setelah Update
- Tekan **Ctrl + F5** (hard refresh) untuk membersihkan cache browser

### Data Transaksi Tidak Tersimpan
- Periksa koneksi jaringan lokal
- Pastikan MySQL di Laragon masih berjalan (tidak berhenti sendiri)
- Coba ulangi transaksi

---

## Bantuan Lebih Lanjut

Jika mengalami kendala yang tidak tercantum di panduan ini, hubungi:

- **WhatsApp Toko:** 082315580021
- **Lokasi:** Pasar Karangampel Blok D4-D6
- Hubungi **Admin** atau **Owner** untuk mendapat bantuan teknis

---

*Panduan ini dibuat khusus untuk sistem POS Toko Ade Fitri. Versi dokumen: 1.0 — Agustus 2026*
