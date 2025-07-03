<?php
// pages/product_detail.php

session_start();
require_once '../config/database.php';

$product_data = null;
$error_message = '';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    $_SESSION['message'] = "ID Produk tidak valid.";
    $_SESSION['message_type'] = "danger";
    header('Location: products.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT p.*, c.category_name, b.brand_name 
                           FROM products p
                           LEFT JOIN categories c ON p.category_id = c.category_id
                           LEFT JOIN brands b ON p.brand_id = b.brand_id
                           WHERE p.product_id = ?");
    $stmt->execute([$product_id]);
    $product_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product_data) {
        $_SESSION['message'] = "Produk tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header('Location: products.php');
        exit;
    }

} catch (PDOException $e) {
    $error_message = "Error database: " . $e->getMessage();
}

include_once '../includes/header.php';
?>

<div class="container container-main">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="home.php">Beranda</a></li>
            <li class="breadcrumb-item"><a href="products.php">Produk</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product_data['product_name']); ?></li>
        </ol>
    </nav>

    <?php if ($error_message): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <?php if ($product_data): ?>
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <img src="<?php echo '../' . htmlspecialchars($product_data['image_url'] ?: 'assets/img/placeholder.png'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product_data['product_name']); ?>" style="max-height: 400px; object-fit: contain; padding: 20px;">
            </div>
        </div>
        <div class="col-md-6">
            <h1><?php echo htmlspecialchars($product_data['product_name']); ?></h1>
            <p class="text-muted">Merek: <?php echo htmlspecialchars($product_data['brand_name'] ?: 'N/A'); ?> | Kategori: <?php echo htmlspecialchars($product_data['category_name'] ?: 'N/A'); ?></p>
            <h2 class="product-price">Rp <?php echo number_format($product_data['price'], 0, ',', '.'); ?></h2>
            <p><strong>Stok:</strong> <?php echo ($product_data['stock'] > 0) ? htmlspecialchars($product_data['stock']) : '<span class="text-danger">Habis</span>'; ?></p>
            
            <p><?php echo nl2br(htmlspecialchars($product_data['description'])); ?></p>

            <?php if (!empty($product_data['specs'])): ?>
            <h5>Spesifikasi Teknis:</h5>
            <ul class="list-group list-group-flush mb-4">
                <?php 
                $specs_array = @json_decode($product_data['specs'], true); // Coba decode JSON
                if (is_array($specs_array) && !empty($specs_array)):
                    foreach ($specs_array as $key => $value): ?>
                        <li class="list-group-item"><strong><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $key))); ?>:</strong> <?php echo htmlspecialchars($value); ?></li>
                    <?php endforeach;
                else: // Jika bukan JSON, tampilkan sebagai teks biasa
                    echo '<li class="list-group-item">' . nl2br(htmlspecialchars($product_data['specs'])) . '</li>';
                endif;
                ?>
            </ul>
            <?php endif; ?>

            <?php if ($product_data['stock'] > 0): ?>
                <form action="cart.php?action=add" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product_data['product_id']; ?>">
                    <div class="form-group row">
                        <label for="quantity" class="col-sm-2 col-form-label">Jumlah</label>
                        <div class="col-sm-4">
                            <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?php echo htmlspecialchars($product_data['stock']); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-cart-plus"></i> Tambah ke Keranjang</button>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg" disabled>Stok Habis</button>
            <?php endif; ?>

        </div>
    </div>

    <h3 class="mt-5 mb-3">Ulasan Pelanggan</h3>
    <div class="card mb-4">
        <div class="card-body">
            <p class="text-muted">Fitur ulasan akan ditampilkan di sini.</p>
            <div class="media mb-3">
                <img src="https://via.placeholder.com/50" class="mr-3 rounded-circle" alt="User Avatar">
                <div class="media-body">
                    <h6 class="mt-0">Nama Pengguna <small class="text-muted">(Tanggal Ulasan)</small></h6>
                    <div class="text-warning">
                        <i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="far fa-star"></i> (4/5)
                    </div>
                    Produk ini sangat bagus, saya suka sekali!
                </div>
            </div>
            <div class="media">
                <img src="https://via.placeholder.com/50" class="mr-3 rounded-circle" alt="User Avatar">
                <div class="media-body">
                    <h6 class="mt-0">Pengguna Lain <small class="text-muted">(Tanggal Ulasan)</small></h6>
                    <div class="text-warning">
                        <i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="fas fa-star"></i> (5/5)
                    </div>
                    Sesuai deskripsi, pengiriman cepat.
                </div>
            </div>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                <hr>
                <h5>Tulis Ulasan Anda</h5>
                <form action="submit_review.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product_data['product_id']; ?>">
                    <div class="form-group">
                        <label for="rating">Rating</label>
                        <select class="form-control" id="rating" name="rating" required>
                            <option value="5">5 Bintang (Sangat Bagus)</option>
                            <option value="4">4 Bintang (Bagus)</option>
                            <option value="3">3 Bintang (Cukup)</option>
                            <option value="2">2 Bintang (Kurang)</option>
                            <option value="1">1 Bintang (Buruk)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="comment">Komentar</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Kirim Ulasan</button>
                </form>
            <?php else: ?>
                <p class="mt-3 text-center text-muted">Login untuk menulis ulasan.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php endif; ?>
</div>

<?php include_once '../includes/footer.php'; ?>