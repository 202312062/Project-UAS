<?php
// admin/users/index.php

session_start();
require_once '../../includes/auth.php'; // Perhatikan path, ini dari sub-folder admin

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php'); // Kembali ke login utama
    exit;
}

// Sertakan koneksi database
require_once '../../config/database.php'; // Perhatikan path

// Ambil data pengguna dari database
$users = [];
try {
    $stmt = $pdo->query("SELECT user_id, username, email, role FROM users ORDER BY user_id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    // Anda bisa mengarahkan ke halaman error atau menampilkan pesan yang lebih user-friendly
}

include_once '../../includes/header.php'; // Header umum
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../../includes/sidebar.php'; // Sidebar umum ?>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manajemen Pengguna</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="create.php" class="btn btn-sm btn-success">
                        <i class="fas fa-plus-circle"></i> Tambah Pengguna Baru
                    </a>
                </div>
            </div>

            <?php
            // Tampilkan pesan sukses/error (misalnya dari proses create/edit/delete)
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
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $user['user_id']; ?>" class="btn btn-info btn-sm">Edit</a>
                                        <a href="delete.php?id=<?php echo $user['user_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">Tidak ada data pengguna.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>