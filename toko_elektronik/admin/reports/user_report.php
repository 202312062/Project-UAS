<?php
// admin/reports/user_report.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$users_report = [];
$error_message = '';

try {
    // Mengambil data pengguna dari tabel 'users'
    // Jika Anda memiliki tabel 'customers' yang berelasi dengan 'users',
    // Anda dapat melakukan JOIN untuk menampilkan detail pelanggan juga.
    // Contoh ini mengambil dari tabel 'users' saja untuk kesederhanaan,
    // asumsikan peran 'customer' juga ada di tabel 'users'.
    $stmt = $pdo->query("SELECT user_id, username, email, role FROM users ORDER BY user_id ASC");
    $users_report = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Error database: " . $e->getMessage();
}

include_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../../includes/sidebar.php'; ?>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Laporan Pengguna</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header">
                    Daftar Semua Pengguna
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>ID Pengguna</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users_report) > 0): ?>
                                    <?php foreach ($users_report as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">Tidak ada data pengguna.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <a href="index.php" class="btn btn-secondary">Kembali ke Laporan Utama</a>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>