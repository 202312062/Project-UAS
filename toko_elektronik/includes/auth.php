<?php
// includes/auth.php

// Pastikan session sudah dimulai di halaman yang memanggil auth.php
// Jika belum, Anda bisa tambahkan: session_start(); di sini, tapi lebih baik di awal setiap halaman

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasRole($required_role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $required_role;
}

// Fungsi redirect jika tidak login atau tidak punya role (Opsional)
function redirectToLogin($is_admin_area = false) {
    if ($is_admin_area) {
        header('Location: ../../login.php'); // Dari subfolder admin ke root login
    } else {
        header('Location: login.php'); // Dari root atau pages ke root login
    }
    exit;
}

// Contoh penggunaan:
// require_once '../../includes/auth.php'; // di admin/
// if (!isLoggedIn() || !hasRole('admin')) {
//     redirectToLogin(true); // true karena dari area admin
// }
?>