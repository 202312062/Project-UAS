<?php
// admin/products/index.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$products = [];
try {
    // Ambil data produk beserta nama kategori dan merek
    $stmt = $pdo->query("SELECT p.product_id, p.product_name, p.price, p.stock, c.category_name, b.brand_name 
                         FROM products p
                         LEFT JOIN categories c ON p.category_id = c.category_id
                         LEFT JOIN brands b ON p.brand_id = b.brand_id
                         ORDER BY p.product_id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <h1 class="h2">Manajemen Produk</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="create.php" class="btn btn-sm btn-success">
                        <i class="fas fa-plus-circle"></i> Tambah Produk Baru
                    </a>
                </div>
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
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Merek</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['product_id']); ?></td>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($product['brand_name'] ?? 'N/A'); ?></td>
                                    <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($product['stock']); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $product['product_id']; ?>" class="btn btn-info btn-sm">Edit</a>
                                        <a href="delete.php?id=<?php echo $product['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">Tidak ada data produk.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>