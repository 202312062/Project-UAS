<?php
// admin/categories/delete.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    $_SESSION['message'] = "ID Kategori tidak valid untuk dihapus.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit;
}

try {
    // Opsional: Sebelum menghapus kategori, Anda mungkin ingin:
    // 1. Mengatur `category_id` produk yang terkait menjadi NULL, ATAU
    // 2. Mencegah penghapusan jika ada produk yang masih menggunakan kategori ini.
    // Contoh untuk mengatur ke NULL:
    $pdo->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?")->execute([$category_id]);


    $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
    if ($stmt->execute([$category_id])) {
        $_SESSION['message'] = "Kategori berhasil dihapus.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal menghapus kategori.";
        $_SESSION['message_type'] = "danger";
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "Error database: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
}

header('Location: index.php');
exit;