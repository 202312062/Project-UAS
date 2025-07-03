<?php
// admin/reports/stock_report.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$stock_data = [];
$error_message = '';

try {
    // Laporan stok rendah (misal, stok di bawah 10)
    $stmt = $pdo->query("SELECT product_id, product_name, stock, price 
                         FROM products 
                         WHERE stock <= 10 
                         ORDER BY stock ASC");
    $stock_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Anda bisa juga mengambil semua produk untuk laporan stok umum
    // $stmt_all = $pdo->query("SELECT product_id, product_name, stock, price FROM products ORDER BY stock ASC");
    // $all_stock_data = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

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
                <h1 class="h2">Laporan Stok Produk</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header">
                    Produk dengan Stok Rendah (Stok &le; 10)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>ID Produk</th>
                                    <th>Nama Produk</th>
                                    <th>Stok Saat Ini</th>
                                    <th>Harga Satuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($stock_data) > 0): ?>
                                    <?php foreach ($stock_data as $data): ?>
                                        <tr class="<?php echo ($data['stock'] <= 5) ? 'table-danger' : 'table-warning'; ?>">
                                            <td><?php echo htmlspecialchars($data['product_id']); ?></td>
                                            <td><?php echo htmlspecialchars($data['product_name']); ?></td>
                                            <td><?php echo htmlspecialchars($data['stock']); ?></td>
                                            <td>Rp <?php echo number_format($data['price'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">Tidak ada produk dengan stok rendah.</td>
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