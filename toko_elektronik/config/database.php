<?php
// config/database.php

$host = 'localhost'; // Ganti jika host database berbeda
$db = 'toko_elektronik'; // Ganti dengan nama database Anda
$user = 'root'; // Ganti dengan username database Anda
$pass = ''; // Ganti dengan password database Anda
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Jika koneksi gagal, hentikan eksekusi dan tampilkan pesan error
    die("Koneksi database gagal: " . $e->getMessage());
}
?>