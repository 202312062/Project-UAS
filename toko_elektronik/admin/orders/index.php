<?php
// admin/orders/index.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$orders = [];
try {
    // Ambil data pesanan dan info pelanggan
    $stmt = $pdo->query("SELECT o.order_id, c.full_name AS customer_name, o.order_date, o.total_amount, o.order_status 
                         FROM orders o
                         JOIN customers c ON o.customer_id = c.customer_id
                         ORDER BY o.order_date DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

include_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../../includes/sidebar.php'; ?>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manajemen Pesanan</h1>
            </div>

            <?php
            if (isset($_SESSION['message'])) {
                echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show" role="alert">' . $_SESSION['message'] . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>

            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Tanggal Pesan</th>
                            <th>Jumlah Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $order): ?>
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
                                    <td>
                                        <a href="view.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary btn-sm">Lihat Detail</a>
                                        <a href="update_status.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info btn-sm">Ubah Status</a>
                                        </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">Tidak ada data pesanan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>