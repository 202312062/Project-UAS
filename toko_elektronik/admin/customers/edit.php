<?php
// admin/customers/edit.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$error_message = '';
$customer_data = null;
$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($customer_id <= 0) {
    $_SESSION['message'] = "ID Pelanggan tidak valid.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit;
}

// Ambil data pelanggan
try {
    $stmt = $pdo->prepare("SELECT c.*, u.email, u.username FROM customers c JOIN users u ON c.user_id = u.user_id WHERE c.customer_id = ?");
    $stmt->execute([$customer_id]);
    $customer_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer_data) {
        $_SESSION['message'] = "Pelanggan tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $error_message = "Error database: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone_number = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']); // Update juga email di tabel users
    $username = trim($_POST['username']); // Update juga username di tabel users

    // Validasi
    if (empty($full_name) || empty($phone_number) || empty($address) || empty($email) || empty($username)) {
        $error_message = "Semua field wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } else {
        try {
            $pdo->beginTransaction(); // Mulai transaksi

            // Update tabel customers
            $stmt_cust = $pdo->prepare("UPDATE customers SET full_name = ?, phone_number = ?, address = ? WHERE customer_id = ?");
            $stmt_cust->execute([$full_name, $phone_number, $address, $customer_id]);

            // Update tabel users (sesuai user_id yang terkait dengan customer)
            $stmt_user = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
            $stmt_user->execute([$username, $email, $customer_data['user_id']]);

            $pdo->commit(); // Commit transaksi jika berhasil

            $_SESSION['message'] = "Data pelanggan berhasil diperbarui.";
            $_SESSION['message_type'] = "success";
            header('Location: index.php');
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack(); // Rollback transaksi jika ada error
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
                <h1 class="h2">Edit Pelanggan</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($customer_data): ?>
            <form action="edit.php?id=<?php echo htmlspecialchars($customer_data['customer_id']); ?>" method="POST">
                <div class="form-group">
                    <label for="full_name">Nama Lengkap</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($customer_data['full_name']); ?>">
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($customer_data['username']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($customer_data['email']); ?>">
                </div>
                <div class="form-group">
                    <label for="phone_number">Nomor Telepon</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($customer_data['phone_number']); ?>">
                </div>
                <div class="form-group">
                    <label for="address">Alamat</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($customer_data['address']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>