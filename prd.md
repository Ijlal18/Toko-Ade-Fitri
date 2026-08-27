# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## Toko Ade Fitri - Sistem Point of Sale (POS)

---

**Dokumen Versi:** 1.0
**Tanggal:** 20 Agustus 2026
**Status:** Final
**Project Owner:** Ade Fitri
**Prepared By:** Development Team

---

## DAFTAR ISI

1. [Pendahuluan](#1-pendahuluan)
2. [Tujuan Proyek](#2-tujuan-proyek)
3. [Lingkup Proyek](#3-lingkup-proyek)
4. [Stakeholder](#4-stakeholder)
5. [Fitur & Fungsionalitas](#5-fitur--fungsionalitas)
6. [User Roles & Permissions](#6-user-roles--permissions)
7. [User Interface & Design](#7-user-interface--design)
8. [Arsitektur Sistem](#8-arsitektur-sistem)
9. [Database Design](#9-database-design)
10. [API Specifications](#10-api-specifications)
11. [Non-Functional Requirements](#11-non-functional-requirements)
12. [Timeline & Milestones](#12-timeline--milestones)
13. [Risiko & Mitigasi](#13-risiko--mitigasi)
14. [Lampiran](#14-lampiran)

---

## 1. PENDAHULUAN

### 1.1 Latar Belakang
Toko Ade Fitri adalah toko retail yang menjual berbagai macam produk kebutuhan sehari-hari seperti plastik, sembako, bahan kue, minuman, dan lainnya. Saat ini toko masih menggunakan sistem manual dalam mencatat transaksi, stok, dan laporan keuangan yang menyebabkan:

- Kesalahan pencatatan manual
- Sulitnya melacak stok produk
- Laporan keuangan yang tidak akurat
- Waktu pelayanan customer yang lama
- Sulitnya menganalisis performa toko

### 1.2 Visi Proyek
Membangun sistem Point of Sale (POS) yang terintegrasi untuk meningkatkan efisiensi operasional toko, akurasi data, dan pengalaman pelayanan customer.

### 1.3 Misi Proyek
- Mengotomatisasi proses transaksi penjualan
- Menyediakan data real-time untuk pengambilan keputusan
- Meningkatkan akurasi manajemen stok dan keuangan
- Memudahkan pembuatan laporan toko

### 1.4 Definisi & Singkatan
| Istilah | Definisi |
|---------|----------|
| POS | Point of Sale - Sistem kasir |
| PRD | Product Requirements Document |
| DDD | Domain-Driven Design |
| API | Application Programming Interface |
| RBAC | Role-Based Access Control |
| QRIS | Quick Response Code Indonesian Standard |
| SKU | Stock Keeping Unit |

---

## 2. TUJUAN PROYEK

### 2.1 Tujuan Utama
1. Menyediakan sistem POS yang cepat dan mudah digunakan
2. Mengotomatisasi manajemen stok produk
3. Menyediakan laporan keuangan dan toko secara real-time
4. Meningkatkan efisiensi operasional toko minimal 50%
5. Mengurangi kesalahan pencatatan hingga 90%

### 2.2 Tujuan Bisnis
- Meningkatkan kecepatan transaksi dari 5 menit menjadi 1 menit
- Menyediakan data akurat untuk pengambilan keputusan
- Meningkatkan kepuasan pelanggan
- Memudahkan monitoring performa toko

### 2.3 Success Metrics
| Metrik | Target | Pengukuran |
|--------|--------|------------|
| Waktu Transaksi | < 1 menit | Rata-rata waktu per transaksi |
| Akurasi Data | > 99% | Validasi data entry |
| User Adoption | 100% karyawan | Semua staff menggunakan sistem |
| Uptime Sistem | 99.9% | Monitoring server |
| Laporan Real-time | Instant | Waktu generate laporan |

---

## 3. LINGKUP PROYEK

### 3.1 In Scope (Dalam Lingkup)
- Sistem Point of Sale (POS) dengan multiple payment methods
- Manajemen produk, kategori, supplier
- Manajemen stok dan suplai
- Riwayat transaksi dengan invoice
- Laporan toko (harian, mingguan, bulanan, tahunan, custom)
- Dashboard dengan data real-time
- Manajemen customer
- Manajemen user dengan 3 roles (Admin, Owner, Karyawan)
- Notifikasi real-time untuk perubahan data
- Export laporan ke PDF/Excel

### 3.2 Out of Scope (Di Luar Lingkup)
- Mobile app (hanya web responsive)
- Marketplace integration
- Sistem akuntansi full
- E-commerce website
- Integration with external ERP

---

## 4. STAKEHOLDER

### 4.1 Daftar Stakeholder
| Stakeholder | Peran | Kebutuhan |
|-------------|------|-----------|
| Ade Fitri | Pemilik Toko | Laporan keuangan, monitoring toko |
| Owner | Pemilik/Manejemen | Dashboard, laporan, analisis bisnis |
| Admin | Pengelola Sistem | Manajemen data, user management |
| Karyawan | Pengguna POS | Transaksi cepat, stok mudah |
| Customer | Pelanggan | Pelayanan cepat, invoice jelas |
| Supplier | Pemasok | Data produk dan stok |

### 4.2 Stakeholder Requirements Matrix
| Kebutuhan | Owner | Admin | Karyawan | Customer |
|-----------|-------|-------|----------|----------|
| Transaksi Cepat | ✓ | ✓ | ✓ | ✓ |
| Laporan Akurat | ✓ | ✓ | | |
| Stok Real-time | ✓ | ✓ | ✓ | |
| Dashboard | ✓ | ✓ | | |
| Manajemen Data | | ✓ | | |

---

## 5. FITUR & FUNGSIONALITAS

### 5.1 Dashboard
**ID:** F-001
**Prioritas:** High
**User Story:** Sebagai pemilik/admin, saya ingin melihat ringkasan toko dalam satu halaman

#### Fungsionalitas:
1. **Statistik Ringkas**
   - Total transaksi hari ini
   - Omzet hari ini
   - Rata-rata transaksi
   - Customer baru

2. **Grafik Omzet**
   - Omzet harian (7 hari terakhir)
   - Omzet mingguan
   - Omzet bulanan

3. **Produk Terlaris**
   - Top 5 produk terlaris
   - Diagram batang

4. **Notifikasi Stok**
   - Produk dengan stok menipis
   - Alert stok minimum

5. **Aktifitas Terakhir**
   - Transaksi terbaru
   - Aktivitas stok

#### Wireframe: