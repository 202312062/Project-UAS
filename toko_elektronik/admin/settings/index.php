<?php
// admin/settings/index.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$error_message = '';
$success_message = '';
$settings = []; // Array untuk menyimpan pengaturan dari DB (jika ada tabel settings)

// Contoh: Ambil pengaturan dari tabel `settings` (Anda perlu membuat tabel ini)
/*
CREATE TABLE settings (
    setting_key VARCHAR(255) PRIMARY KEY,
    setting_value TEXT
);
INSERT INTO settings (setting_key, setting_value) VALUES ('site_name', 'E-GadgetStore');
INSERT INTO settings (setting_key, setting_value) VALUES ('contact_email', 'admin@egadgetstore.com');
*/

try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    $error_message = "Error mengambil pengaturan: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = trim($_POST['site_name']);
    $contact_email = trim($_POST['contact_email']);
    // Tambahkan pengaturan lain sesuai kebutuhan

    if (empty($site_name) || empty($contact_email)) {
        $error_message = "Nama situs dan email kontak wajib diisi.";
    } elseif (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email kontak tidak valid.";
    } else {
        try {
            // Update atau insert pengaturan
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                   ON DUPLICATE KEY UPDATE setting_value = ?");
            
            $stmt->execute(['site_name', $site_name, $site_name]);
            $stmt->execute(['contact_email', $contact_email, $contact_email]);

            $_SESSION['message'] = "Pengaturan berhasil diperbarui.";
            $_SESSION['message_type'] = "success";
            header('Location: index.php'); // Redirect untuk menampilkan pesan
            exit;

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
                <h1 class="h2">Pengaturan Sistem</h1>
            </div>

            <?php
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

            <form action="index.php" method="POST">
                <div class="form-group">
                    <label for="site_name">Nama Situs</label>
                    <input type="text" class="form-control" id="site_name" name="site_name" required value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="contact_email">Email Kontak</label>
                    <input type="email" class="form-control" id="contact_email" name="contact_email" required value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
            </form>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>