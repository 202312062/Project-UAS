<?php
// admin/brands/delete.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$brand_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($brand_id <= 0) {
    $_SESSION['message'] = "ID Merek tidak valid untuk dihapus.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit;
}

try {
    // Opsional: Sebelum menghapus merek, Anda mungkin ingin:
    // 1. Mengatur `brand_id` produk yang terkait menjadi NULL, ATAU
    // 2. Mencegah penghapusan jika ada produk yang masih menggunakan merek ini.
    // Contoh untuk mengatur ke NULL:
    $pdo->prepare("UPDATE products SET brand_id = NULL WHERE brand_id = ?")->execute([$brand_id]);

    $stmt = $pdo->prepare("DELETE FROM brands WHERE brand_id = ?");
    if ($stmt->execute([$brand_id])) {
        $_SESSION['message'] = "Merek berhasil dihapus.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal menghapus merek.";
        $_SESSION['message_type'] = "danger";
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "Error database: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
}

header('Location: index.php');
exit;