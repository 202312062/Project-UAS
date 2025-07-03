<?php
// register.php

require_once 'config/database.php';

$error_message = '';
$success_message = '';

// Jika pengguna sudah login, arahkan ke halaman profil
if (isset($_SESSION['user_id'])) {
    header('Location: pages/profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $phone_number = trim($_POST['phone_number']);
    $address = trim($_POST['address']);

    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($full_name) || empty($phone_number) || empty($address)) {
        $error_message = "Semua field wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password minimal 6 karakter.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Konfirmasi password tidak cocok.";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $pdo->beginTransaction(); // Mulai transaksi

            // Cek apakah username atau email sudah ada
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt_check->execute([$username, $email]);
            if ($stmt_check->fetchColumn() > 0) {
                $error_message = "Username atau email sudah digunakan.";
                $pdo->rollBack(); // Rollback jika username/email sudah ada
            } else {
                // Insert ke tabel users
                $stmt_user = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'customer')");
                $stmt_user->execute([$username, $email, $hashed_password]);
                $new_user_id = $pdo->lastInsertId();

                // Insert ke tabel customers
                $stmt_customer = $pdo->prepare("INSERT INTO customers (user_id, full_name, phone_number, address) VALUES (?, ?, ?, ?)");
                $stmt_customer->execute([$new_user_id, $full_name, $phone_number, $address]);

                $pdo->commit(); // Commit transaksi jika semua berhasil

                $_SESSION['message'] = "Pendaftaran berhasil! Silakan login.";
                $_SESSION['message_type'] = "success";
                header('Location: login.php');
                exit;
            }
        } catch (PDOException $e) {
            $pdo->rollBack(); // Rollback jika ada error database
            $error_message = "Error pendaftaran: " . $e->getMessage();
        }
    }
}

include_once 'includes/header.php';
?>

<div class="container container-main" style="max-width: 600px; margin-top: 50px;">
    <h1 class="my-4 text-center">Daftar Akun Baru</h1>

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

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="register.php" method="POST">
                <h5>Informasi Akun</h5>
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
                    <small class="form-text text-muted">Minimal 6 karakter.</small>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>

                <h5 class="mt-4">Informasi Pribadi</h5>
                <div class="form-group">
                    <label for="full_name">Nama Lengkap</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="phone_number">Nomor Telepon</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" required value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="address">Alamat Lengkap</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block mt-4">Daftar</button>
            </form>
            <p class="mt-3 text-center">Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>