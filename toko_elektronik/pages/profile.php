<?php
// pages/profile.php

session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Hanya izinkan pelanggan yang sudah login untuk mengakses halaman ini
if (!isLoggedIn() || !hasRole('customer')) {
    $_SESSION['message'] = "Anda perlu login sebagai pelanggan untuk melihat profil.";
    $_SESSION['message_type'] = "warning";
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$customer_data = [];
$user_email = '';
$user_username = '';
$customer_orders = [];
$error_message = '';
$success_message = '';

// Ambil parameter tab dari URL
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'info'; // Default tab: info

try {
    // Ambil data dari tabel users
    $stmt_user = $pdo->prepare("SELECT username, email FROM users WHERE user_id = ?");
    $stmt_user->execute([$user_id]);
    $user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);
    if ($user_info) {
        $user_username = $user_info['username'];
        $user_email = $user_info['email'];
    }

    // Ambil data dari tabel customers
    $stmt_customer = $pdo->prepare("SELECT customer_id, full_name, phone_number, address FROM customers WHERE user_id = ?");
    $stmt_customer->execute([$user_id]);
    $customer_data = $stmt_customer->fetch(PDO::FETCH_ASSOC);

    // Set customer_id di sesi jika belum ada (berguna untuk cart/checkout)
    if ($customer_data && !isset($_SESSION['customer_id'])) {
        $_SESSION['customer_id'] = $customer_data['customer_id'];
    } else if (!$customer_data) {
        // Jika user login tapi belum ada di tabel customers, ini adalah anomali
        $error_message = "Data pelanggan Anda tidak ditemukan. Harap hubungi admin.";
    }

    // Ambil riwayat pesanan jika tab adalah 'orders'
    if ($current_tab === 'orders' && $customer_data) {
        $stmt_orders = $pdo->prepare("SELECT order_id, order_date, total_amount, order_status 
                                      FROM orders 
                                      WHERE customer_id = ? 
                                      ORDER BY order_date DESC");
        $stmt_orders->execute([$customer_data['customer_id']]);
        $customer_orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error_message = "Error database: " . $e->getMessage();
}

// Logika untuk Update Profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $current_tab === 'info') {
    $full_name = trim($_POST['full_name']);
    $phone_number = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password']; // Password jika ingin diubah

    // Validasi
    if (empty($full_name) || empty($phone_number) || empty($address) || empty($email) || empty($username)) {
        $error_message = "Semua field informasi dasar wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } elseif (!empty($password) && strlen($password) < 6) {
        $error_message = "Password baru minimal 6 karakter jika diisi.";
    } else {
        try {
            $pdo->beginTransaction();

            // Update users table
            $sql_user = "UPDATE users SET username = ?, email = ?";
            $params_user = [$username, $email];
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql_user .= ", password = ?";
                $params_user[] = $hashed_password;
            }
            $sql_user .= " WHERE user_id = ?";
            $params_user[] = $user_id;
            $stmt_update_user = $pdo->prepare($sql_user);
            $stmt_update_user->execute($params_user);

            // Update customers table
            $stmt_update_customer = $pdo->prepare("UPDATE customers SET full_name = ?, phone_number = ?, address = ? WHERE user_id = ?");
            $stmt_update_customer->execute([$full_name, $phone_number, $address, $user_id]);

            $pdo->commit();
            $success_message = "Profil Anda berhasil diperbarui.";
            
            // Perbarui data di sesi setelah update
            $_SESSION['username'] = $username; 
            $_SESSION['email'] = $email; // Jika Anda menyimpan email di sesi

            // Refresh data setelah update
            $stmt_user = $pdo->prepare("SELECT username, email FROM users WHERE user_id = ?");
            $stmt_user->execute([$user_id]);
            $user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);
            if ($user_info) {
                $user_username = $user_info['username'];
                $user_email = $user_info['email'];
            }
            $stmt_customer = $pdo->prepare("SELECT customer_id, full_name, phone_number, address FROM customers WHERE user_id = ?");
            $stmt_customer->execute([$user_id]);
            $customer_data = $stmt_customer->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = "Error saat memperbarui profil: " . $e->getMessage();
        }
    }
}


include_once '../includes/header.php';
?>

<div class="container container-main">
    <h1 class="my-4 text-center">Profil Saya</h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="profile.php?tab=info" class="list-group-item list-group-item-action <?php echo ($current_tab == 'info') ? 'active' : ''; ?>">Informasi Akun</a>
                <a href="profile.php?tab=orders" class="list-group-item list-group-item-action <?php echo ($current_tab == 'orders') ? 'active' : ''; ?>">Pesanan Saya</a>
                <a href="../logout.php" class="list-group-item list-group-item-action text-danger">Logout</a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if ($current_tab == 'info'): ?>
                        <h4>Informasi Akun Anda</h4>
                        <?php if ($customer_data): ?>
                            <form action="profile.php?tab=info" method="POST">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user_username); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">Password (Biarkan kosong jika tidak ingin diubah)</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <small class="form-text text-muted">Isi hanya jika ingin mengubah password.</small>
                                </div>
                                <div class="form-group">
                                    <label for="full_name">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($customer_data['full_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone_number">Nomor Telepon</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($customer_data['phone_number']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="address">Alamat</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($customer_data['address']); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Perbarui Profil</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">Data profil Anda belum lengkap. Silakan hubungi admin atau update.</div>
                            <?php endif; ?>

                    <?php elseif ($current_tab == 'orders'): ?>
                        <h4>Riwayat Pesanan Saya</h4>
                        <?php if (count($customer_orders) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID Pesanan</th>
                                            <th>Tanggal</th>
                                            <th>Total Jumlah</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customer_orders as $order): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                                <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                                <td>
                                                    <span class="badge 
                                                        <?php 
                                                        if ($order['order_status'] == 'pending') echo 'badge-warning';
                                                        else if ($order['order_status'] == 'completed') echo 'badge-success';
                                                        else if ($order['order_status'] == 'cancelled') echo 'badge-danger';
                                                        else echo 'badge-secondary';
                                                        ?>">
                                                        <?php echo htmlspecialchars(ucfirst($order['order_status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="view_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info btn-sm">Lihat Detail</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center">Anda belum memiliki riwayat pesanan.</div>
                        <?php endif; ?>

                    <?php elseif ($current_tab == 'reviews'): ?>
                        <h4>Ulasan Saya</h4>
                        <p class="text-muted">Fitur ulasan yang Anda buat akan ditampilkan di sini.</p>
                        <div class="alert alert-info text-center">Fitur ini belum diimplementasikan sepenuhnya.</div>
                        <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>