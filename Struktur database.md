# 2. Struktur Database Proyek Toko Elektronik (E-GadgetStore)

Database ini dirancang untuk mendukung fungsionalitas inti sebuah toko elektronik, mencakup manajemen pengguna, produk, pesanan, keranjang belanja, ulasan, serta log aktivitas dan pembayaran.

## Penjelasan Tabel

Berikut adalah detail dan fungsi dari setiap tabel dalam database E-GadgetStore:

### 1. `users`

* **Fungsi**: Menyimpan data dasar untuk akun pengguna, baik itu admin maupun pelanggan, termasuk informasi login dan peran akses.
* **Kolom Penting**:
    * `user_id` (PRIMARY KEY, AUTO_INCREMENT): ID unik untuk setiap pengguna.
    * `username` (UNIQUE, NOT NULL): Nama pengguna untuk login.
    * `email` (UNIQUE, NOT NULL): Alamat email pengguna.
    * `password` (NOT NULL): Hash password pengguna.
    * `role` (ENUM('admin', 'customer'), NOT NULL, DEFAULT 'customer'): Peran pengguna dalam sistem.
    * `created_at` (TIMESTAMP): Waktu akun dibuat.
* **Relasi**:
    * `ONE-TO-ONE` dengan `customers` (melalui `user_id`).
    * `ONE-TO-MANY` dengan `activity_log` (melalui `user_id` di `activity_log`, jika digunakan).

### 2. `customers`

* **Fungsi**: Menyimpan informasi pribadi detail dari setiap pelanggan yang terdaftar. Ini memisahkan detail pelanggan dari data login dasar di tabel `users`.
* **Kolom Penting**:
    * `customer_id` (PRIMARY KEY, AUTO_INCREMENT): ID unik untuk setiap pelanggan.
    * `user_id` (FOREIGN KEY, UNIQUE, NOT NULL): Merujuk ke `user_id` di tabel `users`.
    * `full_name`: Nama lengkap pelanggan.
    * `phone_number`: Nomor telepon pelanggan.
    * `address`: Alamat lengkap pelanggan.
* **Relasi**:
    * `ONE-TO-ONE` dengan `users`.
    * `ONE-TO-MANY` dengan `orders` (melalui `customer_id`).
    * `ONE-TO-ONE` dengan `carts` (melalui `customer_id`).
    * `ONE-TO-MANY` dengan `reviews` (melalui `customer_id`).

### 3. `categories`

* **Fungsi**: Mengelompokkan produk ke dalam kategori yang relevan untuk navigasi dan filter yang lebih baik.
* **Kolom Penting**:
    * `category_id` (PRIMARY KEY, AUTO_INCREMENT): ID unik kategori.
    * `category_name` (UNIQUE, NOT NULL): Nama kategori (misal: "Smartphone", "Laptop").
* **Relasi**:
    * `ONE-TO-MANY` dengan `products` (melalui `category_id` di `products`).

### 4. `brands`

* **Fungsi**: Menyimpan daftar merek atau produsen produk elektronik.
* **Kolom Penting**:
    * `brand_id` (PRIMARY KEY, AUTO_INCREMENT): ID unik merek.
    * `brand_name` (UNIQUE, NOT NULL): Nama merek (misal: "Samsung", "Apple", "Xiaomi").
* **Relasi**:
    * `ONE-TO-MANY` dengan `products` (melalui `brand_id` di `products`).

### 5. `products`

* **Fungsi**: Menyimpan semua detail tentang produk elektronik yang dijual di toko, termasuk informasi dasar, harga, stok, gambar, dan spesifikasi teknis.
* **Kolom Penting**:
    * `product_id` (PRIMARY KEY, AUTO_INCREMENT): ID unik produk.
    * `category_id` (FOREIGN KEY): Kategori produk.
    * `brand_id` (FOREIGN KEY): Merek produk.
    * `product_name` (NOT NULL): Nama produk.
    * `description`: Deskripsi lengkap produk.
    * `price` (NOT NULL): Harga jual produk.
    * `stock` (NOT NULL, DEFAULT 0): Jumlah stok produk yang tersedia.
    * `image_url`: Path atau URL gambar produk.
    * `specs` (JSON): Spesifikasi teknis produk dalam format JSON.
    * `created_at` (TIMESTAMP): Waktu produk ditambahkan.
    * `updated_at` (TIMESTAMP): Waktu terakhir produk diperbarui.
* **Relasi**:
    * `MANY-TO-ONE` dengan `categories` dan `brands`.
    * `ONE-TO-MANY` dengan `order_details`, `cart_items`, dan `reviews`.

### 6. `orders`

* **Fungsi**: Mencatat setiap pesanan yang dilakukan oleh pelanggan.
* **Kolom Penting**:
    * `order_id` (PRIMARY KEY, AUTO_INCREMENT): ID unik pesanan.
    * `customer_id` (FOREIGN KEY, NOT NULL): Pelanggan yang membuat pesanan.
    * `order_date` (DATETIME, NOT NULL): Tanggal dan waktu pesanan dibuat.
    * `total_amount` (NOT NULL): Total nilai pesanan.
    * `order_status` (ENUM, NOT NULL, DEFAULT 'pending'): Status pesanan (misal: 'pending', 'processing', 'completed').
    * `delivery_address` (NOT NULL): Alamat pengiriman untuk pesanan ini.
* **Relasi**:
    * `MANY-TO-ONE` dengan `customers`.
    * `ONE-TO-MANY` dengan `order_details`.
    * `ONE-TO-ONE` dengan `payments` (jika digunakan).

### 7. `order_details`

* **Fungsi**: Merinci setiap item produk yang termasuk dalam suatu pesanan. Ini adalah tabel perantara untuk relasi *many-to-many* antara `orders` dan `products`.
* **Kolom Penting**:
    * `order_detail_id` (PRIMARY KEY, AUTO_INCREMENT): ID unik detail pesanan.
    * `order_id` (FOREIGN KEY, NOT NULL): Merujuk ke pesanan induk.
    * `product_id` (FOREIGN KEY, NOT NULL): Merujuk ke produk yang dipesan.
    * `quantity` (NOT NULL): Jumlah produk yang dipesan.
    * `unit_price` (NOT NULL): Harga satuan produk saat pesanan dibuat (bisa berbeda dari harga saat ini di tabel `products`).
* **Relasi**:
    * `MANY-TO-ONE` dengan `orders` dan `products`.

### 8. `carts`

* **Fungsi**: Mewakili keranjang belanja aktif untuk setiap pelanggan yang login.
* **Kolom Penting**:
    * `cart_id` (PRIMARY KEY, AUTO_INCREMENT): ID unik keranjang.
    * `customer_id` (FOREIGN KEY, UNIQUE, NOT NULL): Pelanggan yang memiliki keranjang ini.
    * `created_at` (TIMESTAMP): Waktu keranjang dibuat.
    * `updated_at` (TIMESTAMP): Waktu terakhir keranjang diperbarui.
* **Relasi**:
    * `ONE-TO-ONE` dengan `customers`.
    * `ONE-TO-MANY` dengan `cart_items`.

### 9. `cart_items`

* **Fungsi**: Menyimpan daftar produk dan kuantitasnya yang saat ini ada di dalam keranjang belanja seorang pelanggan. Ini adalah tabel perantara untuk relasi *many-to-many* antara `carts` dan `products`.
* **Kolom Penting**:
    * `cart_item_id` (PRIMARY KEY, AUTO_INCREMENT): ID unik item keranjang.
    * `cart_id` (FOREIGN KEY, NOT NULL): Merujuk ke keranjang induk.
    * `product_id` (FOREIGN KEY, NOT NULL): Merujuk ke produk di keranjang.
    * `quantity` (NOT NULL): Jumlah produk di keranjang.
* **Relasi**:
    * `MANY-TO-ONE` dengan `carts` dan `products`.

### 10. `reviews`

* **Fungsi**: Menyimpan ulasan dan rating yang diberikan oleh pelanggan terhadap produk.
* **Kolom Penting**:
    * `review_id` (PRIMARY KEY, AUTO_INCREMENT): ID unik ulasan.
    * `product_id` (FOREIGN KEY, NOT NULL): Produk yang diulas.
    * `customer_id` (FOREIGN KEY, NOT NULL): Pelanggan yang menulis ulasan.
    * `rating` (NOT NULL, CHECK 1-5): Penilaian produk (skala 1 sampai 5).
    * `comment`: Teks ulasan.
    * `review_date` (TIMESTAMP): Tanggal ulasan diberikan.
* **Relasi**:
    * `MANY-TO-ONE` dengan `products` dan `customers`.

### Tabel Opsional

#### `payments`

* **Fungsi**: Menyimpan detail setiap transaksi pembayaran yang terkait dengan pesanan.
* **Kolom Penting**:
    * `payment_id` (PRIMARY KEY, AUTO_INCREMENT): ID unik pembayaran.
    * `order_id` (FOREIGN KEY, UNIQUE, NOT NULL): Pesanan yang terkait dengan pembayaran ini.
    * `payment_date` (DATETIME, NOT NULL): Tanggal dan waktu pembayaran.
    * `payment_method` (NOT NULL): Metode pembayaran yang digunakan.
    * `amount_paid` (NOT NULL): Jumlah uang yang dibayarkan.
    * `transaction_id`: ID transaksi dari gateway pembayaran (jika ada).
    * `payment_status` (ENUM): Status pembayaran (misal: 'pending', 'completed').
* **Relasi**:
    * `ONE-TO-ONE` dengan `orders`.

#### `activity_log`

* **Fungsi**: Mencatat berbagai aktivitas penting yang terjadi dalam sistem untuk tujuan monitoring dan audit.
* **Kolom Penting**:
    * `log_id` (PRIMARY KEY, AUTO_INCREMENT): ID unik entri log.
    * `user_id` (FOREIGN KEY, NULLABLE): Pengguna yang melakukan aktivitas (bisa NULL jika aktivitas sistem).
    * `activity_type` (NOT NULL): Jenis aktivitas (misal: 'LOGIN', 'PRODUCT_ADDED').
    * `description`: Deskripsi detail aktivitas.
    * `timestamp` (DATETIME): Waktu aktivitas terjadi.
    * `ip_address`: Alamat IP dari mana aktivitas dilakukan.
* **Relasi**:
    * `MANY-TO-ONE` dengan `users`.

## Diagram ERD

Berikut adalah representasi visual dari Entity Relationship Diagram (ERD) untuk database E-GadgetStore. Anda dapat menyalin kode DSL di bawah ini dan menempelkannya langsung ke [dbdiagram.io](https://dbdiagram.io/) untuk melihat diagram interaktif.
![Untitled](https://github.com/user-attachments/assets/cf274167-7445-457b-a6e6-3eeab9813b10)
