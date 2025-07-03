<?php
// admin/products/delete.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    $_SESSION['message'] = "ID Produk tidak valid untuk dihapus.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit;
}

try {
    // Ambil URL gambar sebelum menghapus produk
    $stmt_image = $pdo->prepare("SELECT image_url FROM products WHERE product_id = ?");
    $stmt_image->execute([$product_id]);
    $product_image = $stmt_image->fetch(PDO::FETCH_ASSOC);

    // Hapus produk dari database
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    if ($stmt->execute([$product_id])) {
        // Hapus file gambar dari server jika ada
        if ($product_image && !empty($product_image['image_url']) && file_exists("../../" . $product_image['image_url'])) {
            unlink("../../" . $product_image['image_url']);
        }
        $_SESSION['message'] = "Produk berhasil dihapus.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal menghapus produk.";
        $_SESSION['message_type'] = "danger";
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "Error database: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
}

header('Location: index.php');
exit;