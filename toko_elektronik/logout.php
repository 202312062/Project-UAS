<?php
// logout.php

session_start();

// Hapus semua variabel sesi
$_SESSION = array();

// Hancurkan sesi
session_destroy();

// Hapus cookie sesi.
// Perhatian: Ini akan menghancurkan sesi, bukan hanya data sesi.
// if (ini_get("session.use_cookies")) {
//     $params = session_get_cookie_params();
//     setcookie(session_name(), '', time() - 42000,
//         $params["path"], $params["domain"],
//         $params["secure"], $params["httponly"]
//     );
// }

// Redirect ke halaman login atau beranda
header('Location: login.php');
exit;
?>