<?php
// admin/reports/product_sales_report.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$sales_data = [];
$error_message = '';

try {
    // Laporan sederhana: Produk terlaris berdasarkan total kuantitas terjual
    $stmt = $pdo->query("SELECT p.product_id, p.product_name, SUM(od.quantity) AS total_quantity_sold, SUM(od.quantity * od.unit_price) AS total_revenue
                         FROM order_details od
                         JOIN products p ON od.product_id = p.product_id
                         GROUP BY p.product_id, p.product_name
                         ORDER BY total_quantity_sold DESC, total_revenue DESC");
    $sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <h1 class="h2">Laporan Penjualan Produk</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header">
                    Produk Terlaris (Berdasarkan Kuantitas Terjual)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>ID Produk</th>
                                    <th>Nama Produk</th>
                                    <th>Total Kuantitas Terjual</th>
                                    <th>Total Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($sales_data) > 0): ?>
                                    <?php foreach ($sales_data as $data): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($data['product_id']); ?></td>
                                            <td><?php echo htmlspecialchars($data['product_name']); ?></td>
                                            <td><?php echo htmlspecialchars($data['total_quantity_sold']); ?></td>
                                            <td>Rp <?php echo number_format($data['total_revenue'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">Tidak ada data penjualan produk.</td>
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