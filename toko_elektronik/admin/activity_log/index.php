<?php
// admin/activity_log/index.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$activity_logs = [];
$error_message = '';

// Anda perlu membuat tabel activity_log
/*
CREATE TABLE activity_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity_type VARCHAR(255) NOT NULL,
    description TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);
*/

try {
    // Ambil log aktivitas, gabungkan dengan username jika user_id ada
    $stmt = $pdo->query("SELECT al.log_id, al.activity_type, al.description, al.timestamp, al.ip_address, u.username
                         FROM activity_log al
                         LEFT JOIN users u ON al.user_id = u.user_id
                         ORDER BY al.timestamp DESC
                         LIMIT 100"); // Batasi jumlah log untuk performa
    $activity_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error mengambil log aktivitas: " . $e->getMessage();
}

include_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../../includes/sidebar.php'; ?>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Log Aktivitas</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID Log</th>
                            <th>Waktu</th>
                            <th>Tipe Aktivitas</th>
                            <th>Deskripsi</th>
                            <th>Pengguna</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($activity_logs) > 0): ?>
                            <?php foreach ($activity_logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['log_id']); ?></td>
                                    <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                    <td><?php echo htmlspecialchars($log['activity_type']); ?></td>
                                    <td><?php echo htmlspecialchars($log['description']); ?></td>
                                    <td><?php echo htmlspecialchars($log['username'] ?? 'Guest'); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">Tidak ada log aktivitas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>