<?php
// pages/cart.php

session_start();
require_once '../config/database.php';

$cart_items = [];
$total_cart_amount = 0;
$error_message = '';
$success_message = '';

// Jika pengguna tidak login, keranjang akan disimpan di session (contoh sederhana)
// Jika pengguna login, keranjang harus diambil dari tabel 'carts' dan 'cart_items'
$is_logged_in = isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer';
$customer_id = $is_logged_in ? $_SESSION['customer_id'] : null; // Asumsi customer_id ada di sesi jika role customer

// --- Logika Menambah Produk ke Keranjang ---
if (isset($_GET['action']) && $_GET['action'] == 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);

    if ($product_id && $quantity > 0) {
        try {
            $stmt_product = $pdo->prepare("SELECT product_name, price, stock, image_url FROM products WHERE product_id = ?");
            $stmt_product->execute([$product_id]);
            $product_info = $stmt_product->fetch(PDO::FETCH_ASSOC);

            if ($product_info && $product_info['stock'] >= $quantity) {
                if ($is_logged_in) {
                    // Logic untuk menyimpan ke database (tabel carts & cart_items)
                    // 1. Cek apakah customer sudah punya cart_id
                    $stmt_cart = $pdo->prepare("SELECT cart_id FROM carts WHERE customer_id = ?");
                    $stmt_cart->execute([$customer_id]);
                    $cart_id = $stmt_cart->fetchColumn();

                    if (!$cart_id) {
                        // Jika belum, buat cart baru
                        $stmt_create_cart = $pdo->prepare("INSERT INTO carts (customer_id) VALUES (?)");
                        $stmt_create_cart->execute([$customer_id]);
                        $cart_id = $pdo->lastInsertId();
                    }

                    // 2. Cek apakah produk sudah ada di cart_items
                    $stmt_item = $pdo->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
                    $stmt_item->execute([$cart_id, $product_id]);
                    $existing_item = $stmt_item->fetch(PDO::FETCH_ASSOC);

                    if ($existing_item) {
                        // Jika sudah ada, update kuantitas
                        $new_quantity = $existing_item['quantity'] + $quantity;
                        if ($product_info['stock'] >= $new_quantity) {
                            $stmt_update_item = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
                            $stmt_update_item->execute([$new_quantity, $existing_item['cart_item_id']]);
                            $success_message = "Kuantitas produk berhasil diperbarui di keranjang.";
                        } else {
                            $error_message = "Stok tidak cukup untuk menambahkan jumlah tersebut.";
                        }
                    } else {
                        // Jika belum ada, tambahkan item baru
                        $stmt_insert_item = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
                        $stmt_insert_item->execute([$cart_id, $product_id, $quantity]);
                        $success_message = "Produk berhasil ditambahkan ke keranjang.";
                    }

                } else {
                    // Logic untuk menyimpan ke session (untuk guest)
                    if (!isset($_SESSION['cart'])) {
                        $_SESSION['cart'] = [];
                    }
                    if (isset($_SESSION['cart'][$product_id])) {
                        $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
                        if ($product_info['stock'] >= $new_quantity) {
                            $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
                            $success_message = "Kuantitas produk berhasil diperbarui di keranjang sesi.";
                        } else {
                             $error_message = "Stok tidak cukup untuk menambahkan jumlah tersebut.";
                        }
                    } else {
                        $_SESSION['cart'][$product_id] = [
                            'product_name' => $product_info['product_name'],
                            'price' => $product_info['price'],
                            'image_url' => $product_info['image_url'],
                            'quantity' => $quantity,
                            'product_id' => $product_id // Tambahkan ID untuk referensi
                        ];
                        $success_message = "Produk berhasil ditambahkan ke keranjang sesi.";
                    }
                }
            } else {
                $error_message = "Produk tidak ditemukan atau stok tidak mencukupi.";
            }
        } catch (PDOException $e) {
            $error_message = "Error database: " . $e->getMessage();
        }
    } else {
        $error_message = "Data produk atau kuantitas tidak valid.";
    }
}

// --- Logika Memperbarui Kuantitas Produk di Keranjang ---
if (isset($_GET['action']) && $_GET['action'] == 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id_to_update = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    $new_quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);

    if ($product_id_to_update && $new_quantity >= 0) {
        try {
            $stmt_product = $pdo->prepare("SELECT stock FROM products WHERE product_id = ?");
            $stmt_product->execute([$product_id_to_update]);
            $product_stock = $stmt_product->fetchColumn();

            if ($product_stock === false) {
                 $error_message = "Produk tidak ditemukan.";
            } elseif ($new_quantity > $product_stock) {
                $error_message = "Stok tidak mencukupi untuk kuantitas yang diminta.";
            } else {
                if ($is_logged_in) {
                    $stmt_cart = $pdo->prepare("SELECT cart_id FROM carts WHERE customer_id = ?");
                    $stmt_cart->execute([$customer_id]);
                    $cart_id = $stmt_cart->fetchColumn();

                    if ($cart_id) {
                        if ($new_quantity == 0) {
                            $stmt_delete = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
                            $stmt_delete->execute([$cart_id, $product_id_to_update]);
                            $success_message = "Produk berhasil dihapus dari keranjang.";
                        } else {
                            $stmt_update = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?");
                            $stmt_update->execute([$new_quantity, $cart_id, $product_id_to_update]);
                            $success_message = "Kuantitas keranjang berhasil diperbarui.";
                        }
                    } else {
                         $error_message = "Keranjang tidak ditemukan.";
                    }
                } else {
                    if (isset($_SESSION['cart'][$product_id_to_update])) {
                        if ($new_quantity == 0) {
                            unset($_SESSION['cart'][$product_id_to_update]);
                            $success_message = "Produk berhasil dihapus dari keranjang sesi.";
                        } else {
                            $_SESSION['cart'][$product_id_to_update]['quantity'] = $new_quantity;
                            $success_message = "Kuantitas keranjang sesi berhasil diperbarui.";
                        }
                    } else {
                        $error_message = "Produk tidak ada di keranjang sesi.";
                    }
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error database: " . $e->getMessage();
        }
    } else {
        $error_message = "Data produk atau kuantitas tidak valid.";
    }
}

// --- Logika Menghapus Produk dari Keranjang ---
if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    $product_id_to_remove = filter_var($_GET['product_id'], FILTER_VALIDATE_INT);

    if ($product_id_to_remove) {
        if ($is_logged_in) {
            try {
                $stmt_cart = $pdo->prepare("SELECT cart_id FROM carts WHERE customer_id = ?");
                $stmt_cart->execute([$customer_id]);
                $cart_id = $stmt_cart->fetchColumn();

                if ($cart_id) {
                    $stmt_delete = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
                    $stmt_delete->execute([$cart_id, $product_id_to_remove]);
                    $success_message = "Produk berhasil dihapus dari keranjang.";
                } else {
                    $error_message = "Keranjang tidak ditemukan.";
                }
            } catch (PDOException $e) {
                $error_message = "Error database: " . $e->getMessage();
            }
        } else {
            if (isset($_SESSION['cart'][$product_id_to_remove])) {
                unset($_SESSION['cart'][$product_id_to_remove]);
                $success_message = "Produk berhasil dihapus dari keranjang sesi.";
            } else {
                $error_message = "Produk tidak ada di keranjang sesi.";
            }
        }
    } else {
        $error_message = "ID Produk tidak valid untuk dihapus.";
    }
}


// --- Ambil Data Keranjang untuk Ditampilkan ---
// Setelah semua aksi (add/update/remove) selesai, ambil data keranjang terbaru
if ($is_logged_in) {
    try {
        $stmt_cart_id = $pdo->prepare("SELECT cart_id FROM carts WHERE customer_id = ?");
        $stmt_cart_id->execute([$customer_id]);
        $cart_id = $stmt_cart_id->fetchColumn();

        if ($cart_id) {
            $stmt_items = $pdo->prepare("SELECT ci.product_id, ci.quantity, p.product_name, p.price, p.image_url, p.stock
                                        FROM cart_items ci
                                        JOIN products p ON ci.product_id = p.product_id
                                        WHERE ci.cart_id = ?");
            $stmt_items->execute([$cart_id]);
            $cart_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error_message = "Error mengambil data keranjang dari database: " . $e->getMessage();
    }
} else {
    // Jika guest, ambil dari sesi
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $id => $item) {
             // Pastikan stok masih ada sebelum menampilkan di keranjang sesi
            try {
                $stmt_stock = $pdo->prepare("SELECT stock FROM products WHERE product_id = ?");
                $stmt_stock->execute([$id]);
                $current_stock = $stmt_stock->fetchColumn();
                if ($current_stock !== false && $item['quantity'] <= $current_stock) {
                    $cart_items[] = $item; // Tambahkan ke daftar tampil jika stok cukup
                } else {
                    // Jika stok kurang, mungkin hapus dari sesi atau beri peringatan
                    unset($_SESSION['cart'][$id]);
                    $error_message .= "Stok untuk " . htmlspecialchars($item['product_name']) . " tidak cukup, dihapus dari keranjang.<br>";
                }
            } catch (PDOException $e) {
                $error_message .= "Error cek stok: " . $e->getMessage() . "<br>";
            }
        }
    }
}

// Hitung total harga keranjang
foreach ($cart_items as $item) {
    $total_cart_amount += $item['price'] * $item['quantity'];
}

include_once '../includes/header.php';
?>

<div class="container container-main">
    <h1 class="my-4 text-center">Keranjang Belanja Anda</h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info text-center" role="alert">
            Keranjang belanja Anda kosong. <a href="products.php">Mulai belanja sekarang!</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Gambar</th>
                        <th>Harga Satuan</th>
                        <th>Kuantitas</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td>
                                <img src="<?php echo '../' . htmlspecialchars($item['image_url'] ?: 'assets/img/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="max-width: 80px; height: auto;">
                            </td>
                            <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                            <td>
                                <form action="cart.php?action=update" method="POST" class="form-inline">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['product_id']); ?>">
                                    <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" max="<?php echo htmlspecialchars($item['stock'] ?? 999); ?>" class="form-control form-control-sm" style="width: 80px;">
                                    <button type="submit" class="btn btn-sm btn-outline-primary ml-2">Update</button>
                                </form>
                            </td>
                            <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="cart.php?action=remove&product_id=<?php echo htmlspecialchars($item['product_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus produk ini dari keranjang?');">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right"><strong>Total Belanja:</strong></td>
                        <td colspan="2"><strong>Rp <?php echo number_format($total_cart_amount, 0, ',', '.'); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="text-right mt-4">
            <a href="products.php" class="btn btn-secondary">Lanjutkan Belanja</a>
            <?php if ($total_cart_amount > 0): ?>
                <a href="checkout.php" class="btn btn-success">Lanjutkan ke Checkout</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once '../includes/footer.php'; ?>