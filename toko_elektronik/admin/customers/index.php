<?php
// admin/customers/index.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$customers = [];
try {
    // Ambil data pelanggan dan tautkan ke tabel users untuk email/username jika perlu
    $stmt = $pdo->query("SELECT c.customer_id, c.full_name, c.phone_number, c.address, u.email, u.username
                         FROM customers c
                         JOIN users u ON c.user_id = u.user_id
                         ORDER BY c.full_name ASC");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <h1 class="h2">Manajemen Pelanggan</h1>
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
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($customers) > 0): ?>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['customer_id']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['username']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone_number']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['address']); ?></td>
                                    <td>
                                        <a href="view.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-primary btn-sm">Lihat</a>
                                        <a href="edit.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-info btn-sm">Edit</a>
                                        </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">Tidak ada data pelanggan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>