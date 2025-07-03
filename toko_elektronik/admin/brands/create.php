<?php
// admin/brands/create.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_name = trim($_POST['brand_name']);

    if (empty($brand_name)) {
        $error_message = "Nama merek wajib diisi.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO brands (brand_name) VALUES (?)");
            if ($stmt->execute([$brand_name])) {
                $_SESSION['message'] = "Merek berhasil ditambahkan.";
                $_SESSION['message_type'] = "success";
                header('Location: index.php');
                exit;
            } else {
                $error_message = "Gagal menambahkan merek.";
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
                <h1 class="h2">Tambah Merek Baru</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="create.php" method="POST">
                <div class="form-group">
                    <label for="brand_name">Nama Merek</label>
                    <input type="text" class="form-control" id="brand_name" name="brand_name" required value="<?php echo isset($_POST['brand_name']) ? htmlspecialchars($_POST['brand_name']) : ''; ?>">
                </div>
                <button type="submit" class="btn btn-primary">Tambah Merek</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>