<?php
// admin/users/create.php

session_start();
require_once '../../includes/auth.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

require_once '../../config/database.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Password akan dihash
    $role = $_POST['role'];

    // Validasi sederhana
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error_message = "Semua field wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password minimal 6 karakter.";
    } else {
        // Hash password sebelum disimpan
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Cek apakah username atau email sudah ada
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt_check->execute([$username, $email]);
            if ($stmt_check->fetchColumn() > 0) {
                $error_message = "Username atau email sudah digunakan.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$username, $email, $hashed_password, $role])) {
                    $_SESSION['message'] = "Pengguna berhasil ditambahkan.";
                    $_SESSION['message_type'] = "success";
                    header('Location: index.php'); // Kembali ke daftar pengguna
                    exit;
                } else {
                    $error_message = "Gagal menambahkan pengguna.";
                }
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
                <h1 class="h2">Tambah Pengguna Baru</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <form action="create.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="customer" <?php echo (isset($_POST['role']) && $_POST['role'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                        <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Tambah Pengguna</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>