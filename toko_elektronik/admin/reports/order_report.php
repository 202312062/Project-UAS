<?php
// admin/reports/order_report.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$orders_report = [];
$error_message = '';

// Filter untuk laporan (contoh: berdasarkan status)
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all'; // Default 'all'
$allowed_statuses = ['all', 'pending', 'processing', 'shipped', 'completed', 'cancelled'];
if (!in_array($filter_status, $allowed_statuses)) {
    $filter_status = 'all'; // Reset jika status tidak valid
}

try {
    $sql = "SELECT o.order_id, c.full_name AS customer_name, o.order_date, o.total_amount, o.order_status, p.payment_method, p.amount_paid
            FROM orders o
            JOIN customers c ON o.customer_id = c.customer_id
            LEFT JOIN payments p ON o.order_id = p.order_id"; // Join dengan payments jika ada
    
    $params = [];
    if ($filter_status != 'all') {
        $sql .= " WHERE o.order_status = ?";
        $params[] = $filter_status;
    }
    $sql .= " ORDER BY o.order_date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders_report = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <h1 class="h2">Laporan Pesanan</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header">
                    Filter Laporan
                </div>
                <div class="card-body">
                    <form method="GET" action="order_report.php" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="status" class="mr-2">Status Pesanan:</label>
                            <select class="form-control form-control-sm" id="status" name="status">
                                <option value="all" <?php echo ($filter_status == 'all') ? 'selected' : ''; ?>>Semua Status</option>
                                <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo ($filter_status == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo ($filter_status == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                <option value="completed" <?php echo ($filter_status == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo ($filter_status == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Terapkan Filter</button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Daftar Pesanan <?php echo ($filter_status != 'all') ? '(' . ucfirst($filter_status) . ')' : ''; ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Pelanggan</th>
                                    <th>Tanggal</th>
                                    <th>Jumlah Total</th>
                                    <th>Status</th>
                                    <th>Metode Pembayaran</th>
                                    <th>Jumlah Bayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($orders_report) > 0): ?>
                                    <?php foreach ($orders_report as $order): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
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
                                            <td><?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></td>
                                            <td>Rp <?php echo number_format($order['amount_paid'] ?? 0, 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">Tidak ada pesanan dengan kriteria ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <a href="index.php" class="btn btn-secondary">Kembali ke Laporan Utama</a>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>