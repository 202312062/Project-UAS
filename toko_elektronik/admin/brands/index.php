<?php
// admin/brands/index.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$brands = [];
try {
    $stmt = $pdo->query("SELECT brand_id, brand_name FROM brands ORDER BY brand_name ASC");
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <h1 class="h2">Manajemen Merek</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="create.php" class="btn btn-sm btn-success">
                        <i class="fas fa-plus-circle"></i> Tambah Merek Baru
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
                            <th>Nama Merek</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($brands) > 0): ?>
                            <?php foreach ($brands as $brand): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($brand['brand_id']); ?></td>
                                    <td><?php echo htmlspecialchars($brand['brand_name']); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $brand['brand_id']; ?>" class="btn btn-info btn-sm">Edit</a>
                                        <a href="delete.php?id=<?php echo $brand['brand_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus merek ini? Semua produk dari merek ini mungkin akan kehilangan mereknya.');">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">Tidak ada data merek.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>