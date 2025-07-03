<?php
// pages/products.php

session_start();
require_once '../config/database.php';

$products = [];
$error_message = '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$brand_filter = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;

$categories = [];
$brands = [];

try {
    // Ambil daftar kategori dan merek untuk filter
    $stmt_categories = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
    $categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

    $stmt_brands = $pdo->query("SELECT brand_id, brand_name FROM brands ORDER BY brand_name");
    $brands = $stmt_brands->fetchAll(PDO::FETCH_ASSOC);

    // Bangun query produk dengan filter
    $sql = "SELECT p.product_id, p.product_name, p.price, p.image_url, c.category_name, b.brand_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN brands b ON p.brand_id = b.brand_id
            WHERE 1=1"; // Kondisi awal yang selalu true

    $params = [];

    if (!empty($search_query)) {
        $sql .= " AND (p.product_name LIKE ? OR p.description LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }
    if ($category_filter > 0) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_filter;
    }
    if ($brand_filter > 0) {
        $sql .= " AND p.brand_id = ?";
        $params[] = $brand_filter;
    }

    $sql .= " ORDER BY p.product_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}

include_once '../includes/header.php';
?>

<div class="container container-main">
    <h1 class="my-4 text-center">Daftar Produk Elektronik</h1>

    <?php if ($error_message): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Filter dan Pencarian Produk</h5>
            <form method="GET" action="products.php" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <label for="search" class="sr-only">Cari Produk</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Cari nama/deskripsi..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                <div class="form-group mr-3 mb-2">
                    <label for="category" class="sr-only">Kategori</label>
                    <select class="form-control" id="category" name="category">
                        <option value="0">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo ($category_filter == $cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mr-3 mb-2">
                    <label for="brand" class="sr-only">Merek</label>
                    <select class="form-control" id="brand" name="brand">
                        <option value="0">Semua Merek</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo $brand['brand_id']; ?>" <?php echo ($brand_filter == $brand['brand_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand['brand_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2">Terapkan Filter</button>
                <a href="products.php" class="btn btn-outline-secondary mb-2 ml-2">Reset Filter</a>
            </form>
        </div>
    </div>

    <div class="row">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo '../' . htmlspecialchars($product['image_url'] ?: 'assets/img/placeholder.png'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                            <p class="card-text text-muted small"><?php echo htmlspecialchars($product['brand_name'] ?: 'Tanpa Merek'); ?> | <?php echo htmlspecialchars($product['category_name'] ?: 'Tanpa Kategori'); ?></p>
                            <p class="product-price mt-auto">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                            <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary mt-2">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <p>Tidak ada produk yang ditemukan dengan kriteria tersebut.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>