<?php
// admin/categories/edit.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$error_message = '';
$category_data = null;
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    $_SESSION['message'] = "ID Kategori tidak valid.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit;
}

// Ambil data kategori yang akan diedit
try {
    $stmt = $pdo->prepare("SELECT category_id, category_name FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $category_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category_data) {
        $_SESSION['message'] = "Kategori tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $error_message = "Error database: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name']);

    if (empty($category_name)) {
        $error_message = "Nama kategori wajib diisi.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
            if ($stmt->execute([$category_name, $category_id])) {
                $_SESSION['message'] = "Kategori berhasil diperbarui.";
                $_SESSION['message_type'] = "success";
                header('Location: index.php');
                exit;
            } else {
                $error_message = "Gagal memperbarui kategori.";
            }
        } catch (PDOException $e) {
            $error_message = "Error database: " . $e->getMessage();
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
                <h1 class="h2">Edit Kategori</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($category_data): ?>
            <form action="edit.php?id=<?php echo htmlspecialchars($category_data['category_id']); ?>" method="POST">
                <div class="form-group">
                    <label for="category_name">Nama Kategori</label>
                    <input type="text" class="form-control" id="category_name" name="category_name" required value="<?php echo htmlspecialchars($category_data['category_name']); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>