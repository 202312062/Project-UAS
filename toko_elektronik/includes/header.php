<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-GadgetStore</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <?php
    // Tentukan path relatif ke folder assets/css berdasarkan lokasi file saat ini
    $css_path = '';
    if (str_contains($_SERVER['REQUEST_URI'], '/admin/')) {
        $css_path = '../../assets/css/style.css';
    } else if (str_contains($_SERVER['REQUEST_URI'], '/pages/')) {
        $css_path = '../assets/css/style.css';
    } else {
        $css_path = 'assets/css/style.css'; // Untuk root seperti index.php, login.php
    }
    ?>
    <link rel="stylesheet" href="<?php echo $css_path; ?>">
    <?php if (str_contains($_SERVER['REQUEST_URI'], '/admin/')): ?>
        <style>
            /* CSS khusus untuk layout admin (dari header admin sebelumnya, untuk kemudahan) */
            body {
                font-size: .875rem;
            }

            .feather {
                width: 16px;
                height: 16px;
                vertical-align: text-bottom;
            }

            /* Sidebar */
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 100;
                padding: 48px 0 0;
                box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            }

            @media (max-width: 767.98px) {
                .sidebar {
                    top: 5rem;
                }
            }

            .sidebar-sticky {
                position: relative;
                top: 0;
                height: calc(100vh - 48px);
                padding-top: .5rem;
                overflow-x: hidden;
                overflow-y: auto;
            }

            .sidebar .nav-link {
                font-weight: 500;
                color: #333;
            }

            .sidebar .nav-link .feather {
                margin-right: 4px;
                color: #999;
            }

            .sidebar .nav-link.active {
                color: #007bff;
            }

            .sidebar .nav-link:hover .feather,
            .sidebar .nav-link.active .feather {
                color: inherit;
            }

            .sidebar-heading {
                font-size: .75rem;
                text-transform: uppercase;
            }

            /* Navbar Admin */
            .navbar-brand {
                padding-top: .75rem;
                padding-bottom: .75rem;
                font-size: 1rem;
                background-color: rgba(0, 0, 0, .25);
                box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
            }

            .navbar .navbar-toggler {
                top: .25rem;
                right: 1rem;
            }

            .navbar .form-control {
                padding: .75rem 1rem;
                border-width: 0;
                border-radius: 0;
            }

            .form-control-dark {
                color: #fff;
                background-color: rgba(255, 255, 255, .1);
                border-color: rgba(255, 255, 255, .1);
            }

            .form-control-dark:focus {
                border-color: transparent;
                box-shadow: 0 0 0 3px rgba(255, 255, 255, .25);
            }
        </style>
    <?php endif; ?>
</head>
<body>
    <?php if (str_contains($_SERVER['REQUEST_URI'], '/admin/')): ?>
        <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
            <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="<?php echo '../../admin/dashboard.php'; ?>">E-GadgetStore Admin</a>
            <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <input class="form-control form-control-dark w-100" type="text" placeholder="Cari..." aria-label="Search">
            <ul class="navbar-nav px-3">
                <li class="nav-item text-nowrap">
                    <a class="nav-link" href="<?php echo '../../logout.php'; ?>">Keluar</a>
                </li>
            </ul>
        </nav>
    <?php else: ?>
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container">
                <a class="navbar-brand" href="<?php echo (str_contains($_SERVER['REQUEST_URI'], '/pages/')) ? '../index.php' : 'index.php'; ?>">E-GadgetStore</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' || basename($_SERVER['PHP_SELF']) == 'home.php') ? 'active' : ''; ?>">
                            <a class="nav-link" href="<?php echo (str_contains($_SERVER['REQUEST_URI'], '/pages/')) ? '../index.php' : 'index.php'; ?>">Beranda</a>
                        </li>
                        <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'product_detail.php') ? 'active' : ''; ?>">
                            <a class="nav-link" href="<?php echo (str_contains($_SERVER['REQUEST_URI'], '/pages/')) ? 'products.php' : 'pages/products.php'; ?>">Produk</a>
                        </li>
                        <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'cart.php') ? 'active' : ''; ?>">
                            <a class="nav-link" href="<?php echo (str_contains($_SERVER['REQUEST_URI'], '/pages/')) ? 'cart.php' : 'pages/cart.php'; ?>"><i class="fas fa-shopping-cart"></i> Keranjang</a>
                        </li>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                            <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
                                <a class="nav-link" href="<?php echo (str_contains($_SERVER['REQUEST_URI'], '/pages/')) ? 'profile.php' : 'pages/profile.php'; ?>">Profil</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo (str_contains($_SERVER['REQUEST_URI'], '/pages/')) ? '../logout.php' : 'logout.php'; ?>">Logout</a>
                            </li>
                        <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                             <li class="nav-item">
                                <a class="nav-link" href="<?php echo (str_contains($_SERVER['REQUEST_URI'], '/pages/')) ? '../admin/dashboard.php' : 'admin/dashboard.php'; ?>">Admin Panel</a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link" href="<?php echo (str_contains($_SERVER['REQUEST_URI'], '/pages/')) ? '../logout.php' : 'logout.php'; ?>">Logout</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'active' : ''; ?>">
                                <a class="nav-link" href="<?php echo (str_contains($_SERVER['REQUEST_URI'], '/pages/')) ? '../login.php' : 'login.php'; ?>">Login</a>
                            </li>
                            <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'register.php') ? 'active' : ''; ?>">
                                <a class="nav-link" href="<?php echo (str_contains($_SERVER['REQUEST_URI'], '/pages/')) ? '../register.php' : 'register.php'; ?>">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>