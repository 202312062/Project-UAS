<?php
// pages/home.php

require_once 'C:\xampp\htdocs\toko_elektronik\config\database.php'; // Path dari pages/ ke config/

// Ambil beberapa produk terbaru/unggulan untuk ditampilkan di beranda
$featured_products = [];
try {
    $stmt = $pdo->query("SELECT product_id, product_name, price, image_url FROM products ORDER BY product_id DESC LIMIT 8");
    $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

include_once 'C:\xampp\htdocs\toko_elektronik/includes/header.php'; // Path dari pages/ ke includes/
?>

<div class="container container-main">
    <div class="jumbotron text-center bg-light">
        <h1 class="display-4">Selamat Datang di E-GadgetStore!</h1>
        <p class="lead">Temukan berbagai gadget dan elektronik terbaru dengan harga terbaik.</p>
        <hr class="my-4">
        <p>Jelajahi koleksi kami sekarang dan nikmati penawaran spesial!</p>
        <a class="btn btn-primary btn-lg" href="products.php" role="button">Lihat Produk Kami</a>
    </div>

    <h2 class="mt-5 mb-4 text-center">Produk Unggulan</h2>
    <div class="row">
        <?php if (count($featured_products) > 0): ?>
            <?php foreach ($featured_products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo '../' . htmlspecialchars($product['image_url'] ?: 'assets/img/placeholder.png'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                            <p class="product-price mt-auto">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                            <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary mt-2">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <p>Tidak ada produk unggulan untuk ditampilkan.</p>
            </div>
        <?php endif; ?>
    </div>

    </div>

<?php include_once 'C:\xampp\htdocs\toko_elektronik/includes/footer.php'; ?>