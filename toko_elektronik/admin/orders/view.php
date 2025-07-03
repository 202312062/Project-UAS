<?php
// admin/orders/view.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$order_data = null;
$order_details = [];
$error_message = '';

if ($order_id <= 0) {
    $_SESSION['message'] = "ID Pesanan tidak valid.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit;
}

try {
    // Pastikan query ini sudah diubah
    $stmt_order = $pdo->prepare("SELECT o.*,
                                        c.full_name AS customer_name,
                                        c.address AS customer_address,
                                        c.phone_number AS customer_phone,
                                        u.email AS customer_email,    -- Ambil email dari tabel users
                                        u.username AS customer_username -- Ambil username dari tabel users
                                 FROM orders o
                                 JOIN customers c ON o.customer_id = c.customer_id
                                 JOIN users u ON c.user_id = u.user_id -- Lakukan JOIN ke tabel users
                                 WHERE o.order_id = ?");
    $stmt_order->execute([$order_id]);
    $order_data = $stmt_order->fetch(PDO::FETCH_ASSOC);

    if (!$order_data) {
        $_SESSION['message'] = "Pesanan tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header('Location: index.php');
        exit;
    }

    // Ambil detail item pesanan
    $stmt_details = $pdo->prepare("SELECT od.quantity, od.unit_price, p.product_name, p.image_url
                                   FROM order_details od
                                   JOIN products p ON od.product_id = p.product_id
                                   WHERE od.order_id = ?");
    $stmt_details->execute([$order_id]);
    $order_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

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
                <h1 class="h2">Detail Pesanan #<?php echo htmlspecialchars($order_id); ?></h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($order_data): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            Informasi Pesanan
                        </div>
                        <div class="card-body">
                            <p><strong>ID Pesanan:</strong> <?php echo htmlspecialchars($order_data['order_id']); ?></p>
                            <p><strong>Tanggal Pesan:</strong> <?php echo htmlspecialchars($order_data['order_date']); ?></p>
                            <p><strong>Jumlah Total:</strong> Rp <?php echo number_format($order_data['total_amount'], 0, ',', '.'); ?></p>
                            <p><strong>Status:</strong>
                                <span class="badge
                                    <?php
                                    if ($order_data['order_status'] == 'pending') echo 'badge-warning';
                                    else if ($order_data['order_status'] == 'completed') echo 'badge-success';
                                    else if ($order_data['order_status'] == 'cancelled') echo 'badge-danger';
                                    else echo 'badge-secondary';
                                    ?>">
                                    <?php echo htmlspecialchars(ucfirst($order_data['order_status'])); ?>
                                </span>
                            </p>
                            </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            Informasi Pelanggan
                        </div>
                        <div class="card-body">
                            <p><strong>Nama Pelanggan:</strong> <?php echo htmlspecialchars($order_data['customer_name']); ?></p>
                            <p><strong>Username:</strong> <?php echo htmlspecialchars($order_data['customer_username']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order_data['customer_email']); ?></p>
                            <p><strong>Telepon:</strong> <?php echo htmlspecialchars($order_data['customer_phone']); ?></p>
                            <p><strong>Alamat:</strong> <?php echo htmlspecialchars($order_data['customer_address']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Detail Produk
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Gambar</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($order_details) > 0): ?>
                                    <?php foreach ($order_details as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td>
                                                <?php if (!empty($item['image_url'])): ?>
                                                    <img src="<?php echo '../../' . htmlspecialchars($item['image_url']); ?>" alt="Product Image" style="max-width: 50px; height: auto;">
                                                <?php else: ?>
                                                    No Image
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                            <td>Rp <?php echo number_format($item['unit_price'], 0, ',', '.'); ?></td>
                                            <td>Rp <?php echo number_format($item['quantity'] * $item['unit_price'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">Tidak ada item dalam pesanan ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <a href="index.php" class="btn btn-secondary">Kembali ke Daftar Pesanan</a>
            <a href="update_status.php?id=<?php echo $order_data['order_id']; ?>" class="btn btn-info">Ubah Status Pesanan</a>

            <?php endif; ?>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>