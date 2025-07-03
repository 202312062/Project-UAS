# Proyek Toko Elektronik (E-GadgetStore)

Aplikasi web dinamis Toko Elektronik yang dibangun menggunakan HTML, PHP Native (min 7.4), CSS (dengan Bootstrap), dan Database MySQL (min 5.7). Aplikasi ini dilengkapi dengan sistem autentikasi, manajemen data produk, kategori, merek, pesanan, pelanggan, serta laporan dan log aktivitas.

## Daftar Isi
1.  [Cara Instalasi Aplikasi](#1-cara-instalasi-aplikasi)
    * [Persyaratan Sistem](#persyaratan-sistem)
    * [Langkah-langkah Instalasi](#langkah-langkah-instalasi)
    * [Konfigurasi Database](#konfigurasi-database)
    * [Menjalankan Aplikasi](#menjalankan-aplikasi)
2.  [Struktur Database](#2-struktur-database)
    * [Penjelasan Tabel](#penjelasan-tabel)
    * [Diagram ERD](#diagram-erd)
3.  [Cara Menggunakan Aplikasi](#3-cara-menggunakan-aplikasi)
    * [Login dan Autentikasi](#login-dan-autentikasi)
    * [Navigasi Aplikasi](#navigasi-aplikasi)
    * [Modul Administrator](#modul-administrator)
    * [Fitur Pengguna (Pelanggan)](#fitur-pengguna-pelanggan)

---

## 1. Cara Instalasi Aplikasi

### Persyaratan Sistem
* **Web Server:** Apache (Direkomendasikan XAMPP/WAMP/MAMP untuk lingkungan lokal)
* **Bahasa Pemrograman:** PHP versi 7.4 atau lebih tinggi.
* **Database:** MySQL versi 5.7 atau lebih tinggi.
* **Web Browser:** Modern browser (Chrome, Firefox, Edge, Safari).

### Langkah-langkah Instalasi

1.  **Unduh atau Kloning Proyek:**
    * Jika Anda menerima file zip proyek, ekstrak file tersebut ke dalam direktori `htdocs` XAMPP Anda (contoh: `C:\xampp\htdocs\toko_elektronik`).
    * Jika Anda menggunakan Git, kloning repositori ke `htdocs`:
        ```bash
        cd C:\xampp\htdocs
        git clone <URL_REPO_ANDA> toko_elektronik
        ```

2.  **Konfigurasi Web Server (Opsional, jika tidak di root `htdocs`):**
    * Pastikan Apache di XAMPP/WAMP Anda berjalan.
    * Akses proyek melalui `http://localhost/toko_elektronik/`.

### Konfigurasi Database

1.  **Buat Database:**
    * Buka phpMyAdmin (biasanya melalui `http://localhost/phpmyadmin/`).
    * Buat database baru dengan nama `e_gadgetstore_db`.

2.  **Import Skema Tabel:**
    * Di phpMyAdmin, pilih database `e_gadgetstore_db` yang baru Anda buat.
    * Klik tab `SQL`.
    * Salin dan tempel semua perintah `CREATE TABLE` yang telah disediakan (termasuk tabel utama dan opsional seperti `payments` dan `activity_log`).
    * Jalankan perintah SQL tersebut.

3.  **Konfigurasi Koneksi PHP:**
    * Buka file `config/database.php` dalam direktori proyek Anda.
    * Sesuaikan kredensial database (username, password, nama database) jika berbeda dari default XAMPP/WAMP:
        ```php
        // config/database.php
        $host = 'localhost'; // Biasanya localhost
        $db = 'e_gadgetstore_db'; // Pastikan sama dengan nama database yang Anda buat
        $user = 'root'; // Username database Anda (default XAMPP: root)
        $pass = ''; // Password database Anda (default XAMPP: kosong)
        $charset = 'utf8mb4';
        // ... (sisa kode)
        ```

4.  **Tambahkan Pengguna Admin (untuk Login Awal):**
    * Di phpMyAdmin, pilih database `e_gadgetstore_db` dan buka tabel `users`.
    * Klik tab `Insert` atau jalankan perintah SQL berikut di tab `SQL`:
        ```sql
        INSERT INTO users (username, email, password, role) VALUES
        ('admin', 'admin@egadgetstore.com', '$2y$10$wT8fH2pZ1qL5mX0Y7V6b.O.3J7E9B8C7D6E5F4G3H2I1J0K9L8M7N6O5P4Q3R2S', 'admin');
        ```
        * **Catatan:** Password yang di-hash adalah untuk `admin123`.

### Menjalankan Aplikasi

Setelah semua langkah di atas selesai:
1.  Pastikan Apache dan MySQL di XAMPP/WAMP Anda berjalan.
2.  Buka browser web Anda.
3.  Akses URL aplikasi: `http://localhost/toko_elektronik/`
4.  Anda dapat login ke panel admin melalui `http://localhost/toko_elektronik/login.php` dengan kredensial admin yang telah Anda buat.

---

## 2. Struktur Database

Aplikasi ini menggunakan minimal 10 tabel yang saling berelasi untuk mengelola data toko elektronik secara komprehensif.

### Penjelasan Tabel

* **`users`**: Menyimpan data dasar untuk akun pengguna (admin dan pelanggan) dan peran mereka.
* **`customers`**: Menyimpan informasi pribadi detail dari setiap pelanggan yang terdaftar, berelasi satu-ke-satu dengan `users`.
* **`categories`**: Mengelompokkan produk ke dalam kategori (misal: Smartphone, Laptop).
* **`brands`**: Menyimpan daftar merek atau produsen produk elektronik (misal: Samsung, Apple).
* **`products`**: Menyimpan semua detail produk elektronik, termasuk harga, stok, gambar, dan spesifikasi teknis.
* **`orders`**: Mencatat setiap pesanan yang dilakukan oleh pelanggan, termasuk tanggal, total jumlah, status, dan alamat pengiriman.
* **`order_details`**: Merinci setiap item produk yang ada dalam suatu pesanan (kuantitas dan harga satuan).
* **`carts`**: Mewakili keranjang belanja aktif untuk setiap pelanggan yang login.
* **`cart_items`**: Menyimpan daftar produk dan kuantitasnya yang ada di dalam keranjang belanja.
* **`reviews`**: Menyimpan ulasan dan rating yang diberikan oleh pelanggan terhadap produk.
* **`payments` (Opsional)**: Menyimpan detail setiap transaksi pembayaran yang terkait dengan pesanan.
* **`activity_log` (Opsional)**: Mencatat berbagai aktivitas penting dalam sistem untuk monitoring dan audit.

