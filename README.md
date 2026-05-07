# UMKM Insight - Platform Analitik Bisnis UMKM

UMKM Insight adalah platform berbasis web yang membantu pelaku UMKM memantau performa bisnis mereka melalui visualisasi data transaksi riil (simulasi integrasi SmartBank). Proyek ini dikembangkan menggunakan PHP Native dan MySQL sebagai bagian dari tugas besar Rekayasa Perangkat Lunak (RPL).

## 🚀 Fitur Utama
- **Dashboard Analytics**: Ringkasan pendapatan, pengeluaran, dan laba bersih.
- **Laporan Penjualan**: Tren harian dan histori transaksi dengan filter tier (Free vs Premium).
- **Manajemen Arus Kas**: Visualisasi cashflow masuk dan keluar secara mendalam.
- **Performa Produk**: Analisis produk terlaris dan stok.
- **Sistem Operasional**: Pengajuan upgrade tier, sistem notifikasi, dan pusat bantuan.
- **Multi-Role Access**: Admin, Operator, dan Client (UMKM).

## 🛠️ Tech Stack
- **Backend**: PHP 8.x
- **Database**: MySQL (MariaDB)
- **Frontend**: Tailwind CSS, Phosphor Icons
- **Visualisasi**: Chart.js

---

## 💻 Cara Install di Lokal (Setup)

Ikuti langkah-langkah berikut untuk menjalankan proyek di PC Anda:

### 1. Persiapan Server
Gunakan **FlyEnv**, **XAMPP**, atau **Laragon**.
- Pastikan layanan **Apache** dan **MySQL** sudah berjalan.
- Letakkan folder proyek ini di dalam direktori server Anda (misal: `htdocs` untuk XAMPP atau melalui modul *Hosts* di FlyEnv).

### 2. Konfigurasi Database
1. Buka browser dan akses **phpMyAdmin**.
2. Buat database baru dengan nama `umkm_insight`.
3. Impor file database yang terletak di: `dokumentasi/database.sql`.

### 3. Pengaturan Koneksi
Buka file `config/db.php` dan sesuaikan kredensial database Anda jika berbeda:
```php
$host = 'localhost';
$db   = 'umkm_insight';
$user = 'root';
$pass = 'root'; // Gunakan '' jika Anda menggunakan XAMPP default
```

### 4. Jalankan Aplikasi
Akses melalui browser di alamat yang Anda tentukan (misal: `http://localhost/RPL/` atau `https://umkm.test`).

---

## 🔑 Akun Pengujian (Demo)
Anda dapat mencoba sistem dengan akun berikut:

| Role | Username | Password |
| :--- | :--- | :--- |
| **Client (Free)** | `budi` | `password` |
| **Client (Premium)** | `sari` | `password` |
| **Operator** | `op_jaya` | `password` |
| **Admin** | `admin_super` | `password` |

---

## 📂 Struktur Folder
- `/assets`: File CSS, JS, dan Gambar.
- `/config`: Konfigurasi database dan sistem.
- `/controllers`: Logika pemrosesan data.
- `/dokumentasi`: Skema database, perencanaan, dan panduan simulasi.
- `/includes`: Komponen UI yang dapat digunakan kembali (Header, Sidebar, Footer).
- `/Archives`: File prototype HTML asli sebelum migrasi ke PHP.

---
