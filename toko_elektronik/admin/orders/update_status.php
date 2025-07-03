<?php
// admin/orders/update_status.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$order_data = null;
$error_message = '';

if ($order_id <= 0) {
    $_SESSION['message'] = "ID Pesanan tidak valid.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit;
}

// Ambil data pesanan
try {
    $stmt = $pdo->prepare("SELECT order_id, order_status FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order_data) {
        $_SESSION['message'] = "Pesanan tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $error_message = "Error database: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = trim($_POST['order_status']);
    $allowed_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled']; // Sesuaikan status yang valid

    if (!in_array($new_status, $allowed_statuses)) {
        $error_message = "Status tidak valid.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
            if ($stmt->execute([$new_status, $order_id])) {
                $_SESSION['message'] = "Status pesanan berhasil diperbarui menjadi " . ucfirst($new_status) . ".";
                $_SESSION['message_type'] = "success";
                header('Location: view.php?id=' . $order_id); // Kembali ke detail pesanan
                exit;
            } else {
                $error_message = "Gagal memperbarui status pesanan.";
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
                <h1 class="h2">Ubah Status Pesanan #<?php echo htmlspecialchars($order_id); ?></h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($order_data): ?>
            <form action="update_status.php?id=<?php echo htmlspecialchars($order_data['order_id']); ?>" method="POST">
                <div class="form-group">
                    <label for="order_status">Status Saat Ini: 
                        <span class="badge 
                            <?php 
                            if ($order_data['order_status'] == 'pending') echo 'badge-warning';
                            else if ($order_data['order_status'] == 'completed') echo 'badge-success';
                            else if ($order_data['order_status'] == 'cancelled') echo 'badge-danger';
                            else echo 'badge-secondary';
                            ?>">
                            <?php echo htmlspecialchars(ucfirst($order_data['order_status'])); ?>
                        </span>
                    </label>
                    <select class="form-control" id="order_status" name="order_status" required>
                        <option value="pending" <?php echo ($order_data['order_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo ($order_data['order_status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo ($order_data['order_status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                        <option value="completed" <?php echo ($order_data['order_status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($order_data['order_status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Perbarui Status</button>
                <a href="view.php?id=<?php echo htmlspecialchars($order_data['order_id']); ?>" class="btn btn-secondary">Batal</a>
            </form>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>