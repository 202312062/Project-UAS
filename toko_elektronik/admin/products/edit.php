<?php
// admin/products/edit.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$error_message = '';
$product_data = null;
$categories = [];
$brands = [];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    $_SESSION['message'] = "ID Produk tidak valid.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit;
}

// Ambil data produk yang akan diedit
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product_data) {
        $_SESSION['message'] = "Produk tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header('Location: index.php');
        exit;
    }

    // Ambil data kategori dan merek untuk dropdown
    $stmt_categories = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
    $categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

    $stmt_brands = $pdo->query("SELECT brand_id, brand_name FROM brands ORDER BY brand_name");
    $brands = $stmt_brands->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Error database: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
    $category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
    $brand_id = filter_var($_POST['brand_id'], FILTER_VALIDATE_INT);
    $specs = trim($_POST['specs']);
    $current_image_url = $_POST['current_image_url']; // Gambar yang sudah ada

    $new_image_url = $current_image_url; // Default menggunakan gambar yang sudah ada

    // Validasi input
    if (empty($product_name) || empty($description) || $price === false || $stock === false || $category_id === false || $brand_id === false) {
        $error_message = "Semua field wajib diisi dengan format yang benar (Harga dan Stok harus angka).";
    } elseif ($price < 0 || $stock < 0) {
        $error_message = "Harga dan Stok tidak boleh negatif.";
    } else {
        // Handle upload gambar baru (jika ada)
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../../uploads/"; // Folder untuk menyimpan gambar
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_file_name = uniqid('product_') . '.' . $file_extension;
            $target_file = $target_dir . $new_file_name;

            // Hapus gambar lama jika ada dan berhasil upload gambar baru
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                if (!empty($current_image_url) && file_exists("../../" . $current_image_url)) {
                    unlink("../../" . $current_image_url); // Hapus file gambar lama
                }
                $new_image_url = 'uploads/' . $new_file_name;
            } else {
                $error_message = "Gagal mengunggah gambar baru.";
            }
        }

        if (empty($error_message)) {
            try {
                $stmt = $pdo->prepare("UPDATE products SET product_name = ?, description = ?, price = ?, stock = ?, category_id = ?, brand_id = ?, image_url = ?, specs = ? WHERE product_id = ?");
                if ($stmt->execute([$product_name, $description, $price, $stock, $category_id, $brand_id, $new_image_url, $specs, $product_id])) {
                    $_SESSION['message'] = "Produk berhasil diperbarui.";
                    $_SESSION['message_type'] = "success";
                    header('Location: index.php');
                    exit;
                } else {
                    $error_message = "Gagal memperbarui produk.";
                }
            } catch (PDOException $e) {
                $error_message = "Error database: " . $e->getMessage();
            }
        }
    }
}

include_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../../includes/sidebar.php'; ?>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Produk</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($product_data): ?>
            <form action="edit.php?id=<?php echo htmlspecialchars($product_data['product_id']); ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars($product_data['image_url']); ?>">

                <div class="form-group">
                    <label for="product_name">Nama Produk</label>
                    <input type="text" class="form-control" id="product_name" name="product_name" required value="<?php echo htmlspecialchars($product_data['product_name']); ?>">
                </div>
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($product_data['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="category_id">Kategori</label>
                    <select class="form-control" id="category_id" name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo ($product_data['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="brand_id">Merek</label>
                    <select class="form-control" id="brand_id" name="brand_id" required>
                        <option value="">Pilih Merek</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo $brand['brand_id']; ?>" <?php echo ($product_data['brand_id'] == $brand['brand_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand['brand_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Harga</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" required value="<?php echo htmlspecialchars($product_data['price']); ?>">
                </div>
                <div class="form-group">
                    <label for="stock">Stok</label>
                    <input type="number" class="form-control" id="stock" name="stock" required value="<?php echo htmlspecialchars($product_data['stock']); ?>">
                </div>
                <div class="form-group">
                    <label for="specs">Spesifikasi (opsional, format JSON atau teks)</label>
                    <textarea class="form-control" id="specs" name="specs" rows="3"><?php echo htmlspecialchars($product_data['specs']); ?></textarea>
                    <small class="form-text text-muted">Contoh: {"processor": "Intel i7", "ram": "16GB"}</small>
                </div>
                <div class="form-group">
                    <label for="image">Gambar Produk (Biarkan kosong jika tidak ingin diubah)</label>
                    <?php if (!empty($product_data['image_url'])): ?>
                        <p>Gambar saat ini: <img src="<?php echo '../../' . htmlspecialchars($product_data['image_url']); ?>" alt="Product Image" style="max-width: 100px; height: auto;"></p>
                    <?php endif; ?>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">Unggah gambar baru untuk mengganti yang sudah ada.</small>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>