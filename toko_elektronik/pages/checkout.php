<?php
// pages/checkout.php

session_start();
require_once '../includes/auth.php'; // Sertakan autentikasi
require_once '../config/database.php';

// Hanya izinkan pelanggan yang sudah login untuk mengakses halaman ini
if (!isLoggedIn() || !hasRole('customer')) {
    $_SESSION['message'] = "Anda perlu login sebagai pelanggan untuk melanjutkan checkout.";
    $_SESSION['message_type'] = "warning";
    header('Location: ../login.php');
    exit;
}

$customer_id = $_SESSION['customer_id']; // Ambil customer_id dari sesi setelah login
$cart_items = [];
$total_cart_amount = 0;
$customer_data = [];
$error_message = '';
$success_message = '';

// 1. Ambil data keranjang dari database (sudah login, jadi dari DB)
try {
    $stmt_cart_id = $pdo->prepare("SELECT cart_id FROM carts WHERE customer_id = ?");
    $stmt_cart_id->execute([$customer_id]);
    $cart_id = $stmt_cart_id->fetchColumn();

    if (!$cart_id) {
        // Keranjang kosong atau belum ada, redirect kembali
        $_SESSION['message'] = "Keranjang belanja Anda kosong, tidak dapat melanjutkan checkout.";
        $_SESSION['message_type'] = "danger";
        header('Location: cart.php');
        exit;
    }

    $stmt_items = $pdo->prepare("SELECT ci.product_id, ci.quantity, p.product_name, p.price, p.stock
                                FROM cart_items ci
                                JOIN products p ON ci.product_id = p.product_id
                                WHERE ci.cart_id = ?");
    $stmt_items->execute([$cart_id]);
    $cart_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart_items)) {
        $_SESSION['message'] = "Keranjang belanja Anda kosong, tidak dapat melanjutkan checkout.";
        $_SESSION['message_type'] = "danger";
        header('Location: cart.php');
        exit;
    }

    // Periksa kembali stok untuk setiap item di keranjang
    foreach ($cart_items as $item) {
        if ($item['quantity'] > $item['stock']) {
            $error_message .= "Stok untuk produk '" . htmlspecialchars($item['product_name']) . "' tidak cukup. Harap sesuaikan kuantitas di keranjang.<br>";
        }
        $total_cart_amount += $item['price'] * $item['quantity'];
    }

    // Ambil data pelanggan untuk form pengiriman
    $stmt_customer = $pdo->prepare("SELECT full_name, phone_number, address FROM customers WHERE customer_id = ?");
    $stmt_customer->execute([$customer_id]);
    $customer_data = $stmt_customer->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Error saat memuat data: " . $e->getMessage();
}

// 2. Proses Checkout saat Form Disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error_message)) {
    $delivery_address = trim($_POST['delivery_address']);
    $payment_method = trim($_POST['payment_method']);
    
    // Validasi sederhana
    if (empty($delivery_address) || empty($payment_method)) {
        $error_message = "Alamat pengiriman dan metode pembayaran wajib diisi.";
    } else {
        try {
            $pdo->beginTransaction(); // Mulai transaksi database

            // 1. Buat entri baru di tabel `orders`
            $stmt_order = $pdo->prepare("INSERT INTO orders (customer_id, order_date, total_amount, order_status, delivery_address) VALUES (?, NOW(), ?, ?, ?)");
            $stmt_order->execute([$customer_id, $total_cart_amount, 'pending', $delivery_address]);
            $new_order_id = $pdo->lastInsertId();

            // 2. Pindahkan item dari `cart_items` ke `order_details` dan kurangi stok produk
            foreach ($cart_items as $item) {
                // Tambah ke order_details
                $stmt_order_detail = $pdo->prepare("INSERT INTO order_details (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                $stmt_order_detail->execute([$new_order_id, $item['product_id'], $item['quantity'], $item['price']]);

                // Kurangi stok produk
                $stmt_update_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
                $stmt_update_stock->execute([$item['quantity'], $item['product_id']]);
            }

            // 3. Catat pembayaran (opsional, tergantung alur pembayaran)
            $stmt_payment = $pdo->prepare("INSERT INTO payments (order_id, payment_date, payment_method, amount_paid) VALUES (?, NOW(), ?, ?)");
            $stmt_payment->execute([$new_order_id, $payment_method, $total_cart_amount]);

            // 4. Hapus item dari keranjang setelah berhasil checkout
            $stmt_clear_cart = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
            $stmt_clear_cart->execute([$cart_id]);
            // Opsional: Hapus entri di tabel carts jika sudah kosong dan tidak digunakan lagi
            // $pdo->prepare("DELETE FROM carts WHERE cart_id = ?")->execute([$cart_id]);

            $pdo->commit(); // Commit transaksi jika semua berhasil

            $_SESSION['message'] = "Pesanan Anda berhasil dibuat! ID Pesanan: #" . $new_order_id . ".";
            $_SESSION['message_type'] = "success";
            header('Location: profile.php?tab=orders'); // Arahkan ke halaman profil/pesanan saya
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack(); // Rollback jika ada error
            $error_message = "Gagal memproses pesanan: " . $e->getMessage();
        }
    }
}


include_once '../includes/header.php';
?>

<div class="container container-main">
    <h1 class="my-4 text-center">Proses Checkout</h1>

    <?php if ($error_message): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($cart_items)): ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        Detail Pengiriman & Pembayaran
                    </div>
                    <div class="card-body">
                        <form action="checkout.php" method="POST">
                            <h5 class="mb-3">Alamat Pengiriman</h5>
                            <div class="form-group">
                                <label for="delivery_address">Alamat Lengkap</label>
                                <textarea class="form-control" id="delivery_address" name="delivery_address" rows="3" required><?php echo htmlspecialchars($customer_data['address'] ?? ''); ?></textarea>
                            </div>
                            <h5 class="mt-4 mb-3">Metode Pembayaran</h5>
                            <div class="form-group">
                                <label for="payment_method">Pilih Metode Pembayaran</label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="bank_transfer">Transfer Bank</option>
                                    <option value="credit_card">Kartu Kredit</option>
                                    <option value="ovo">OVO</option>
                                    <option value="gopay">GoPay</option>
                                    </select>
                            </div>
                            <button type="submit" class="btn btn-success btn-lg btn-block mt-4">Konfirmasi Pesanan (Rp <?php echo number_format($total_cart_amount, 0, ',', '.'); ?>)</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        Ringkasan Pesanan
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($cart_items as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($item['product_name']); ?> x <?php echo htmlspecialchars($item['quantity']); ?>
                                    <span>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></span>
                                </li>
                            <?php endforeach; ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center font-weight-bold">
                                Total
                                <span>Rp <?php echo number_format($total_cart_amount, 0, ',', '.'); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center" role="alert">
            Keranjang Anda kosong. Tidak ada yang bisa di-checkout.
        </div>
    <?php endif; ?>
</div>

<?php include_once '../includes/footer.php'; ?>