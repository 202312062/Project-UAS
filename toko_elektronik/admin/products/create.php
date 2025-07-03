<?php
// admin/products/create.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$error_message = '';
$categories = [];
$brands = [];

// Ambil data kategori dan merek untuk dropdown
try {
    $stmt_categories = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
    $categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

    $stmt_brands = $pdo->query("SELECT brand_id, brand_name FROM brands ORDER BY brand_name");
    $brands = $stmt_brands->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error mengambil data kategori/merek: " . $e->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
    $category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
    $brand_id = filter_var($_POST['brand_id'], FILTER_VALIDATE_INT);
    $specs = trim($_POST['specs']); // Spesifikasi produk

    $image_url = ''; // Default kosong

    // Validasi input
    if (empty($product_name) || empty($description) || $price === false || $stock === false || $category_id === false || $brand_id === false) {
        $error_message = "Semua field wajib diisi dengan format yang benar (Harga dan Stok harus angka).";
    } elseif ($price < 0 || $stock < 0) {
        $error_message = "Harga dan Stok tidak boleh negatif.";
    } else {
        // Handle upload gambar (opsional)
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../../uploads/"; // Folder untuk menyimpan gambar
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_file_name = uniqid('product_') . '.' . $file_extension;
            $target_file = $target_dir . $new_file_name;

            // Pastikan folder uploads ada dan writable
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = 'uploads/' . $new_file_name; // Simpan path relatif ke database
            } else {
                $error_message = "Gagal mengunggah gambar.";
            }
        }

        if (empty($error_message)) { // Lanjutkan jika tidak ada error upload
            try {
                $stmt = $pdo->prepare("INSERT INTO products (product_name, description, price, stock, category_id, brand_id, image_url, specs) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$product_name, $description, $price, $stock, $category_id, $brand_id, $image_url, $specs])) {
                    $_SESSION['message'] = "Produk berhasil ditambahkan.";
                    $_SESSION['message_type'] = "success";
                    header('Location: index.php');
                    exit;
                } else {
                    $error_message = "Gagal menambahkan produk.";
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
                <h1 class="h2">Tambah Produk Baru</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="create.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="product_name">Nama Produk</label>
                    <input type="text" class="form-control" id="product_name" name="product_name" required value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="category_id">Kategori</label>
                    <select class="form-control" id="category_id" name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
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
                            <option value="<?php echo $brand['brand_id']; ?>" <?php echo (isset($_POST['brand_id']) && $_POST['brand_id'] == $brand['brand_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand['brand_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Harga</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" required value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="stock">Stok</label>
                    <input type="number" class="form-control" id="stock" name="stock" required value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="specs">Spesifikasi (opsional, format JSON atau teks)</label>
                    <textarea class="form-control" id="specs" name="specs" rows="3"><?php echo isset($_POST['specs']) ? htmlspecialchars($_POST['specs']) : ''; ?></textarea>
                    <small class="form-text text-muted">Contoh: {"processor": "Intel i7", "ram": "16GB"}</small>
                </div>
                <div class="form-group">
                    <label for="image">Gambar Produk</label>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">Maks. ukuran file 2MB.</small>
                </div>
                <button type="submit" class="btn btn-primary">Tambah Produk</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>