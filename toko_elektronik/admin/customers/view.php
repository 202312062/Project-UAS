<?php
// admin/customers/view.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$customer_data = null;
$customer_orders = [];
$error_message = '';

if ($customer_id <= 0) {
    $_SESSION['message'] = "ID Pelanggan tidak valid.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit;
}

try {
    // Ambil detail pelanggan
    $stmt_customer = $pdo->prepare("SELECT c.*, u.email, u.username
                                    FROM customers c
                                    JOIN users u ON c.user_id = u.user_id
                                    WHERE c.customer_id = ?");
    $stmt_customer->execute([$customer_id]);
    $customer_data = $stmt_customer->fetch(PDO::FETCH_ASSOC);

    if (!$customer_data) {
        $_SESSION['message'] = "Pelanggan tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header('Location: index.php');
        exit;
    }

    // Ambil riwayat pesanan pelanggan ini
    $stmt_orders = $pdo->prepare("SELECT order_id, order_date, total_amount, order_status 
                                  FROM orders 
                                  WHERE customer_id = ? 
                                  ORDER BY order_date DESC");
    $stmt_orders->execute([$customer_id]);
    $customer_orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Error database: " . $e->getMessage();
}

include_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../../includes/sidebar.php'; ?>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Detail Pelanggan: <?php echo htmlspecialchars($customer_data['full_name']); ?></h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($customer_data): ?>
            <div class="card mb-4">
                <div class="card-header">
                    Informasi Dasar
                </div>
                <div class="card-body">
                    <p><strong>ID Pelanggan:</strong> <?php echo htmlspecialchars($customer_data['customer_id']); ?></p>
                    <p><strong>Nama Lengkap:</strong> <?php echo htmlspecialchars($customer_data['full_name']); ?></p>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($customer_data['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($customer_data['email']); ?></p>
                    <p><strong>Telepon:</strong> <?php echo htmlspecialchars($customer_data['phone_number']); ?></p>
                    <p><strong>Alamat:</strong> <?php echo htmlspecialchars($customer_data['address']); ?></p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Riwayat Pesanan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Tanggal Pesan</th>
                                    <th>Jumlah Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($customer_orders) > 0): ?>
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
                                                <a href="../orders/view.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary btn-sm">Lihat Detail</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">Pelanggan ini belum memiliki pesanan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <a href="index.php" class="btn btn-secondary">Kembali ke Daftar Pelanggan</a>
            <a href="edit.php?id=<?php echo htmlspecialchars($customer_data['customer_id']); ?>" class="btn btn-info">Edit Data Pelanggan</a>

            <?php endif; ?>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>