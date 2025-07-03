<?php
// login.php

session_start();
require_once 'config/database.php';

$error_message = '';

// Jika pengguna sudah login, arahkan ke dashboard yang sesuai
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: C:\xampp\htdocs\toko_elektronik/admin/dashboard.php');
        exit;
    } else { // Asumsi role 'customer' atau lainnya
        header('Location: pages/profile.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = "Username dan password wajib diisi.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Login berhasil
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Jika peran adalah 'customer', ambil juga customer_id
                if ($user['role'] === 'customer') {
                    $stmt_customer_id = $pdo->prepare("SELECT customer_id FROM customers WHERE user_id = ?");
                    $stmt_customer_id->execute([$user['user_id']]);
                    $_SESSION['customer_id'] = $stmt_customer_id->fetchColumn();
                }

                // Redirect berdasarkan peran
                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: pages/profile.php'); // Atau halaman beranda
                }
                exit;
            } else {
                $error_message = "Username atau password salah.";
            }
        } catch (PDOException $e) {
            $error_message = "Error database: " . $e->getMessage();
        }
    }
}

include_once 'includes/header.php';
?>

<div class="container container-main" style="max-width: 500px; margin-top: 50px;">
    <h1 class="my-4 text-center">Login ke E-GadgetStore</h1>

    <?php
    // Tampilkan pesan dari sesi (misalnya dari halaman checkout yang dilindungi)
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show" role="alert">' . $_SESSION['message'] . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <p class="mt-3 text-center">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>