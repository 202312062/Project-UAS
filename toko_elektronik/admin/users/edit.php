<?php
// admin/users/edit.php

session_start();
require_once '../../includes/auth.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

require_once '../../config/database.php';

$error_message = '';
$user_data = null;
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    $_SESSION['message'] = "ID Pengguna tidak valid.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit;
}

// Ambil data pengguna yang akan diedit
try {
    $stmt = $pdo->prepare("SELECT user_id, username, email, role FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        $_SESSION['message'] = "Pengguna tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $error_message = "Error database: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password']; // Password bisa kosong jika tidak diubah

    // Validasi sederhana
    if (empty($username) || empty($email) || empty($role)) {
        $error_message = "Username, Email, dan Role wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } elseif (!empty($password) && strlen($password) < 6) {
        $error_message = "Password baru minimal 6 karakter jika diisi.";
    } else {
        try {
            $sql = "UPDATE users SET username = ?, email = ?, role = ?";
            $params = [$username, $email, $role];

            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql .= ", password = ?";
                $params[] = $hashed_password;
            }
            $sql .= " WHERE user_id = ?";
            $params[] = $user_id;

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $_SESSION['message'] = "Pengguna berhasil diperbarui.";
                $_SESSION['message_type'] = "success";
                header('Location: index.php');
                exit;
            } else {
                $error_message = "Gagal memperbarui pengguna.";
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
                <h1 class="h2">Edit Pengguna</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($user_data): ?>
            <form action="edit.php?id=<?php echo htmlspecialchars($user_data['user_id']); ?>" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($user_data['username']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($user_data['email']); ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password (Biarkan kosong jika tidak ingin diubah)</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <small class="form-text text-muted">Isi hanya jika ingin mengubah password.</small>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="customer" <?php echo ($user_data['role'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                        <option value="admin" <?php echo ($user_data['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>