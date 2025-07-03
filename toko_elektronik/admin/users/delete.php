<?php
// admin/users/delete.php

session_start();
require_once '../../includes/auth.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

require_once '../../config/database.php';

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    $_SESSION['message'] = "ID Pengguna tidak valid untuk dihapus.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit;
}

try {
    // Pencegahan penghapusan diri sendiri
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['message'] = "Anda tidak dapat menghapus akun Anda sendiri.";
        $_SESSION['message_type'] = "warning";
        header('Location: index.php');
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    if ($stmt->execute([$user_id])) {
        $_SESSION['message'] = "Pengguna berhasil dihapus.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal menghapus pengguna.";
        $_SESSION['message_type'] = "danger";
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "Error database: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
}

header('Location: index.php');
exit;