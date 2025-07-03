<?php
// admin/brands/edit.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$error_message = '';
$brand_data = null;
$brand_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($brand_id <= 0) {
    $_SESSION['message'] = "ID Merek tidak valid.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit;
}

// Ambil data merek yang akan diedit
try {
    $stmt = $pdo->prepare("SELECT brand_id, brand_name FROM brands WHERE brand_id = ?");
    $stmt->execute([$brand_id]);
    $brand_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$brand_data) {
        $_SESSION['message'] = "Merek tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $error_message = "Error database: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_name = trim($_POST['brand_name']);

    if (empty($brand_name)) {
        $error_message = "Nama merek wajib diisi.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE brands SET brand_name = ? WHERE brand_id = ?");
            if ($stmt->execute([$brand_name, $brand_id])) {
                $_SESSION['message'] = "Merek berhasil diperbarui.";
                $_SESSION['message_type'] = "success";
                header('Location: index.php');
                exit;
            } else {
                $error_message = "Gagal memperbarui merek.";
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
                <h1 class="h2">Edit Merek</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($brand_data): ?>
            <form action="edit.php?id=<?php echo htmlspecialchars($brand_data['brand_id']); ?>" method="POST">
                <div class="form-group">
                    <label for="brand_name">Nama Merek</label>
                    <input type="text" class="form-control" id="brand_name" name="brand_name" required value="<?php echo htmlspecialchars($brand_data['brand_name']); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>