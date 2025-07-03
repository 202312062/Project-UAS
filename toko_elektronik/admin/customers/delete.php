<?php
// admin/customers/delete.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($customer_id <= 0) {
    $_SESSION['message'] = "ID Pelanggan tidak valid untuk dihapus.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit;
}

try {
    $pdo->beginTransaction(); // Mulai transaksi

    // Dapatkan user_id yang terkait dengan customer_id
    $stmt_get_user_id = $pdo->prepare("SELECT user_id FROM customers WHERE customer_id = ?");
    $stmt_get_user_id->execute([$customer_id]);
    $user_id = $stmt_get_user_id->fetchColumn();

    if ($user_id) {
        // Hapus data terkait di tabel lain terlebih dahulu (jika ada)
        // Contoh: hapus order_details, orders, cart_items, carts, reviews terkait
        $pdo->prepare("DELETE FROM order_details WHERE order_id IN (SELECT order_id FROM orders WHERE customer_id = ?)")->execute([$customer_id]);
        $pdo->prepare("DELETE FROM orders WHERE customer_id = ?")->execute([$customer_id]);
        $pdo->prepare("DELETE FROM cart_items WHERE cart_id IN (SELECT cart_id FROM carts WHERE customer_id = ?)")->execute([$customer_id]);
        $pdo->prepare("DELETE FROM carts WHERE customer_id = ?")->execute([$customer_id]);
        $pdo->prepare("DELETE FROM reviews WHERE customer_id = ?")->execute([$customer_id]);

        // Hapus dari tabel customers
        $stmt_cust = $pdo->prepare("DELETE FROM customers WHERE customer_id = ?");
        $stmt_cust->execute([$customer_id]);

        // Hapus dari tabel users (akun login)
        $stmt_user = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt_user->execute([$user_id]);

        $pdo->commit(); // Commit transaksi jika semua berhasil

        $_SESSION['message'] = "Pelanggan dan semua data terkait berhasil dihapus.";
        $_SESSION['message_type'] = "success";
    } else {
        $pdo->rollBack();
        $_SESSION['message'] = "Gagal menemukan pelanggan atau data terkait.";
        $_SESSION['message_type'] = "danger";
    }

} catch (PDOException $e) {
    $pdo->rollBack(); // Rollback transaksi jika ada error
    $_SESSION['message'] = "Error database saat menghapus pelanggan: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
}

header('Location: index.php');
exit;