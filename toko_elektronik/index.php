<?php
// index.php
session_start(); // Panggil session_start() HANYA DI SINI

// Sertakan header
include_once 'includes/header.php';

// Sertakan halaman beranda
include_once 'pages/home.php'; // Ini akan menyertakan pages/home.php

// Sertakan footer
include_once 'includes/footer.php';
?>