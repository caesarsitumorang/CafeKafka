<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "config/koneksi.php";

// Zona waktu
date_default_timezone_set("Asia/Jakarta");

// Ambil jam operasional cafe
$qCafe = mysqli_query($koneksi, "SELECT jam_buka, jam_closing, bank, no_rek, pemilik FROM data_cafe LIMIT 1");
$dataCafe = mysqli_fetch_assoc($qCafe);
$jam_buka    = $dataCafe['jam_buka'] ?? "08:00:00";
$jam_closing = $dataCafe['jam_closing'] ?? "22:00:00";
$dataPembayaran = $dataCafe;

// Tentukan status cafe
$waktu_sekarang = date("H:i:s");
if ($jam_closing < $jam_buka) {
    if ($waktu_sekarang >= $jam_buka || $waktu_sekarang <= $jam_closing) {
        $status_cafe = "buka";
    } elseif ($waktu_sekarang < $jam_buka) {
        $status_cafe = "belum_buka";
    } else {
        $status_cafe = "tutup";
    }
} else {
    if ($waktu_sekarang >= $jam_buka && $waktu_sekarang <= $jam_closing) {
        $status_cafe = "buka";
    } elseif ($waktu_sekarang < $jam_buka) {
        $status_cafe = "belum_buka";
    } else {
        $status_cafe = "tutup";
    }
}

// Cek login
$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
    exit;
}

// Ambil data pelanggan
$user_query = mysqli_query($koneksi, "
    SELECT p.id_pelanggan, p.detail_alamat, p.latitude, p.longitude
    FROM users u
    JOIN pelanggan p ON p.username = u.username
    WHERE u.id_user = '$id_user'
");
$user_data = mysqli_fetch_assoc($user_query);
$id_pelanggan = $user_data['id_pelanggan'] ?? null;

if (!$id_pelanggan) {
    echo "<script>alert('Data pelanggan tidak ditemukan.'); window.location='index.php';</script>";
    exit;
}

// Cek apakah lokasi pelanggan sudah lengkap
$lokasi_lengkap = !empty($user_data['detail_alamat']) && !empty($user_data['latitude']) && !empty($user_data['longitude']);

// Hapus item
if (isset($_GET['hapus'])) {
    $id_keranjang = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM keranjang WHERE id=$id_keranjang AND id_pelanggan=$id_pelanggan");
    header("Location: index.php?page=keranjang/keranjang");
    exit;
}

// Update jumlah item
if (isset($_POST['update_jumlah'])) {
    foreach($_POST['jumlah'] as $id_keranjang => $jml) {
        $jml = intval($jml);
        if ($jml > 0) {
            mysqli_query($koneksi, "UPDATE keranjang SET jumlah=$jml WHERE id=$id_keranjang AND id_pelanggan=$id_pelanggan");
        }
    }
    header("Location: index.php?page=keranjang/keranjang");
    exit;
}

$success_message = "";
$error_message = "";

// Proses checkout
if (isset($_POST['checkout'])) {
    if (!$lokasi_lengkap) {
        $error_message = "Lengkapi lokasi Anda terlebih dahulu di halaman profil sebelum melakukan pemesanan.";
    } elseif ($status_cafe != "buka") {
        $error_message = "Cafe sedang $status_cafe. Pesanan hanya bisa dilakukan antara jam " . date("H:i", strtotime($jam_buka)) . " - " . date("H:i", strtotime($jam_closing));
    } elseif (!isset($_POST['selected_items']) || empty($_POST['selected_items'])) {
        $error_message = "Pilih minimal satu item untuk checkout!";
    } else {
        $selected_items = $_POST['selected_items'];
        $total_harga = 0;
        $stok_tidak_cukup = [];

        // Update jumlah terbaru
        foreach($_POST['jumlah'] as $id_keranjang => $jml) {
            $jml = intval($jml);
            mysqli_query($koneksi, "UPDATE keranjang SET jumlah=$jml WHERE id=$id_keranjang AND id_pelanggan=$id_pelanggan");
        }

        // Cek stok
        foreach ($selected_items as $id_keranjang) {
            $id_keranjang = intval($id_keranjang);
            $item_query = mysqli_query($koneksi, "
                SELECT 
                    k.id, k.id_makanan, k.id_minuman, k.jumlah,
                    m.nama AS nama_makanan, m.stok AS stok_makanan,
                    n.nama AS nama_minuman, n.stok AS stok_minuman
                FROM keranjang k
                LEFT JOIN makanan m ON k.id_makanan = m.id
                LEFT JOIN minuman n ON k.id_minuman = n.id
                WHERE k.id = $id_keranjang AND k.id_pelanggan = $id_pelanggan
            ");
            if ($item = mysqli_fetch_assoc($item_query)) {
                $stok_tersedia = $item['id_makanan'] ? $item['stok_makanan'] : $item['stok_minuman'];
                if ($stok_tersedia < $item['jumlah']) {
                    $stok_tidak_cukup[] = $item['nama_makanan'] ?? $item['nama_minuman'];
                }
            }
        }

        if (!empty($stok_tidak_cukup)) {
            $error_message = "Pesanan gagal. Stok tidak mencukupi untuk: " . implode(", ", $stok_tidak_cukup);
        } else {
            // Hitung total
            foreach ($selected_items as $id_keranjang) {
                $id_keranjang = intval($id_keranjang);
                $res = mysqli_query($koneksi, "
                    SELECT k.jumlah, m.harga AS harga_makanan, n.harga AS harga_minuman
                    FROM keranjang k
                    LEFT JOIN makanan m ON k.id_makanan = m.id
                    LEFT JOIN minuman n ON k.id_minuman = n.id
                    WHERE k.id = $id_keranjang
                ");
                if ($row = mysqli_fetch_assoc($res)) {
                    $harga = $row['harga_makanan'] ?? $row['harga_minuman'];
                    $total_harga += $harga * $row['jumlah'];
                }
            }

            // Upload bukti pembayaran
            if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
                $ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png'];
                if (in_array($ext, $allowed)) {
                    $bukti = "upload/bukti_" . time() . ".$ext";
                    if (!file_exists('upload')) mkdir('upload');
                    move_uploaded_file($_FILES['bukti']['tmp_name'], $bukti);

                    // Simpan pesanan dengan data lokasi pelanggan
                    $stmt = $koneksi->prepare("
                        INSERT INTO pesanan (id_pelanggan, total_harga, status, bukti_pembayaran, detail_alamat, latitude, longitude, created_at)
                        VALUES (?, ?, 'pending', ?, ?, ?, ?, NOW())
                    ");
                    $stmt->bind_param(
                        "idssss",
                        $id_pelanggan,
                        $total_harga,
                        $bukti,
                        $user_data['detail_alamat'],
                        $user_data['latitude'],
                        $user_data['longitude']
                    );
                    $stmt->execute();
                    $id_pesanan = $stmt->insert_id;

                    // Simpan detail pesanan
                    foreach ($selected_items as $id_keranjang) {
                        $id_keranjang = intval($id_keranjang);
                        mysqli_query($koneksi, "
                            INSERT INTO pesanan_detail (id_pesanan, id_makanan, id_minuman, jumlah)
                            SELECT $id_pesanan, id_makanan, id_minuman, jumlah FROM keranjang WHERE id=$id_keranjang
                        ");
                    }

                    // Hapus keranjang
                    mysqli_query($koneksi, "DELETE FROM keranjang WHERE id_pelanggan=$id_pelanggan");

                    echo "<script>alert('Pesanan berhasil dibuat!, cek pesanan di menu pesanan'); window.location='index.php?page=pesanan/pesanan';</script>";
                    exit;
                } else {
                    $error_message = "Format file tidak didukung!";
                }
            } else {
                $error_message = "Upload bukti pembayaran wajib!";
            }
        }
    }
}

// Ambil data keranjang
$keranjang = mysqli_query($koneksi, "
    SELECT 
        k.id, k.id_makanan, k.id_minuman, k.jumlah, 
        m.nama AS nama_makanan, m.harga AS harga_makanan, m.gambar AS gambar_makanan,
        n.nama AS nama_minuman, n.harga AS harga_minuman, n.gambar AS gambar_minuman
    FROM keranjang k
    LEFT JOIN makanan m ON k.id_makanan = m.id
    LEFT JOIN minuman n ON k.id_minuman = n.id
    WHERE k.id_pelanggan = $id_pelanggan
");
$total_items = mysqli_num_rows($keranjang);
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-orange: #ff6b35;
            --secondary-orange: #ff8c42;
            --light-orange: #fff4f0;
            --dark-orange: #e55a2b;
            --orange-gradient: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            --success-green: #28a745;
            --danger-red: #dc3545;
            --warning-yellow: #ffc107;
            --info-blue: #17a2b8;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
            --text-dark: #2d3436;
            --text-muted: #6c757d;
            --shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .page-header {
            background: var(--orange-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
        }

        .page-header h1 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            opacity: 0.9;
            margin: 0;
        }

        /* Cart Items Section */
        .cart-section {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .cart-section-header {
            background: var(--light-gray);
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .cart-section-header h4 {
            margin: 0;
            color: var(--text-dark);
            font-weight: 600;
        }

        .cart-items-container {
            max-height: 70vh;
            overflow-y: auto;
            padding: 0;
        }

        .cart-items-container::-webkit-scrollbar {
            width: 6px;
        }

        .cart-items-container::-webkit-scrollbar-track {
            background: var(--light-gray);
        }

        .cart-items-container::-webkit-scrollbar-thumb {
            background: var(--primary-orange);
            border-radius: 3px;
        }

        .cart-item {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            background: var(--light-orange);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid var(--border-color);
        }

        .item-details h6 {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .item-price {
            color: var(--primary-orange);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .item-type {
            background: var(--light-orange);
            color: var(--primary-orange);
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border: 1px solid var(--border-color);
            background: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: var(--primary-orange);
            color: white;
            border-color: var(--primary-orange);
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.375rem;
            font-weight: 600;
        }

        .quantity-input:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        }

        .subtotal-display {
            text-align: right;
        }

        .subtotal-amount {
            font-weight: 700;
            color: var(--success-green);
            font-size: 1.1rem;
        }

        .checkbox-custom {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-orange);
        }

        /* Checkout Section */
        .checkout-section {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            position: sticky;
            top: 2rem;
            max-height: 80vh;
            overflow-y: auto;
        }

        .checkout-header {
            background: var(--orange-gradient);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }

        .checkout-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .checkout-body {
            padding: 1.5rem;
        }

        .order-summary {
            background: var(--light-gray);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .summary-item:last-child {
            margin-bottom: 0;
            padding-top: 0.5rem;
            border-top: 1px solid var(--border-color);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .total-amount {
            color: var(--primary-orange);
            font-size: 1.25rem;
            font-weight: 700;
        }

        .btn-custom {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary-custom {
            background: var(--orange-gradient);
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .btn-success-custom {
            background: linear-gradient(135deg, var(--success-green) 0%, #218838 100%);
            color: white;
        }

        .btn-danger-custom {
            background: linear-gradient(135deg, var(--danger-red) 0%, #c82333 100%);
            color: white;
        }

        .btn-warning-custom {
            background: linear-gradient(135deg, var(--warning-yellow) 0%, #e0a800 100%);
            color: white;
        }

        .form-control-custom {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 0.75rem;
        }

        .form-control-custom:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        }

        .payment-info {
            background: var(--light-orange);
            border: 1px solid var(--primary-orange);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .payment-info h6 {
            color: var(--primary-orange);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .select-all-section {
            background: var(--light-gray);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        .empty-cart i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--border-color);
        }

        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid var(--border-color);
            margin-top: 1rem;
        }

        .alert-custom {
            border-radius: 8px;
            border: none;
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .cart-items-container {
                max-height: 50vh;
            }

            .checkout-section {
                position: relative;
                top: 0;
                margin-top: 2rem;
            }

            .cart-item {
                padding: 1rem;
            }

            .item-image {
                width: 60px;
                height: 60px;
            }

            .quantity-input {
                width: 50px;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="cart-section">
                <div class="cart-section-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-list me-2"></i>Daftar Pesanan</h4>
                        <span class="badge bg-primary"><?= $total_items ?> Items</span>
                    </div>
                </div>

                <?php if ($total_items == 0): ?>
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <h4>Keranjang Kosong</h4>
                        <p>Belum ada item yang ditambahkan ke keranjang</p>
                        <a href="index.php" class="btn btn-primary-custom btn-custom">
                            <i class="fas fa-utensils me-2"></i>Mulai Belanja
                        </a>
                    </div>
                <?php else: ?>
                    <form method="post" id="cartForm">
                        <div class="select-all-section">
                            <div class="form-check">
                                <input class="form-check-input checkbox-custom" type="checkbox" id="selectAll">
                                <label class="form-check-label fw-semibold" for="selectAll">
                                    Pilih Semua Item
                                </label>
                            </div>
                        </div>

                        <div class="cart-items-container">
                            <?php while($row = mysqli_fetch_assoc($keranjang)): 
                                $harga = $row['id_makanan'] ? $row['harga_makanan'] : $row['harga_minuman'];
                                $nama = $row['nama_makanan'] ?? $row['nama_minuman'];
                                $gambar = $row['id_makanan'] ? $row['gambar_makanan'] : $row['gambar_minuman'];
                                $tipe = $row['id_makanan'] ? 'Makanan' : 'Minuman';
                                $subtotal = $row['jumlah'] * $harga;
                            ?>
                                <div class="cart-item">
                                    <div class="row align-items-center">
                                        <div class="col-1">
                                            <input type="checkbox" 
                                                   name="selected_items[]" 
                                                   value="<?= $row['id'] ?>" 
                                                   class="form-check-input checkbox-custom item-checkbox" 
                                                   data-subtotal="<?= $subtotal ?>"
                                                   data-price="<?= $harga ?>"
                                                   data-quantity="<?= $row['jumlah'] ?>"
                                                   data-item-id="<?= $row['id'] ?>">
                                        </div>

                                        <div class="col-2">
                                            <img src="<?= $gambar ? 'upload/'.$gambar : 'assets/img/no-image.png' ?>" 
                                                 alt="<?= htmlspecialchars($nama) ?>" 
                                                 class="item-image">
                                        </div>

                                        <div class="col-4">
                                            <div class="item-details">
                                                <h6><?= htmlspecialchars($nama) ?></h6>
                                                <div class="item-price">Rp <?= number_format($harga, 0, ',', '.') ?></div>
                                                <span class="item-type"><?= $tipe ?></span>
                                            </div>
                                        </div>

                                        <div class="col-3">
                                            <div class="quantity-controls justify-content-center">
                                                <button type="button" class="quantity-btn" onclick="decreaseQuantity(<?= $row['id'] ?>)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" 
                                                       name="jumlah[<?= $row['id'] ?>]" 
                                                       value="<?= $row['jumlah'] ?>" 
                                                       min="1" max="99"
                                                       class="form-control quantity-input"
                                                       id="qty_<?= $row['id'] ?>"
                                                       data-price="<?= $harga ?>"
                                                       data-item-id="<?= $row['id'] ?>"
                                                       onchange="updateSubtotalAndTotal(<?= $row['id'] ?>)">
                                                <button type="button" class="quantity-btn" onclick="increaseQuantity(<?= $row['id'] ?>)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="col-2">
                                            <div class="subtotal-display">
                                                <div class="subtotal-amount" id="subtotal_<?= $row['id'] ?>">
                                                    Rp <?= number_format($subtotal, 0, ',', '.') ?>
                                                </div>
                                                <button type="button" 
                                                        class="btn btn-danger-custom btn-sm mt-2"
                                                        onclick="confirmDelete(<?= $row['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <div class="p-3 bg-light border-top">
                            <div class="d-flex justify-content-between">
                                <button type="submit" name="update_jumlah" class="btn btn-warning-custom btn-custom">
                                    <i class="fas fa-sync-alt me-2"></i>Update Keranjang
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="checkout-section">
                <div class="checkout-header">
                    <h4><i class="fas fa-credit-card me-2"></i>Ringkasan Pesanan</h4>
                </div>

                <div class="checkout-body">
                    <?php if($success_message): ?>
                        <div class="alert alert-success alert-custom">
                            <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
                        </div>
                    <?php endif; ?>

                    <?php if($error_message): ?>
                        <div class="alert alert-danger alert-custom">
                            <i class="fas fa-exclamation-triangle me-2"></i><?= $error_message ?>
                        </div>
                    <?php endif; ?>

                    <div class="order-summary">
                        <div class="summary-item">
                            <span>Items Dipilih:</span>
                            <span id="selectedCount">0</span>
                        </div>
                        <div class="summary-item">
                            <span>Subtotal:</span>
                            <span id="subtotalAmount">Rp 0</span>
                        </div>
                        <div class="summary-item">
                            <span>Total:</span>
                            <span class="total-amount" id="totalAmount">Rp 0</span>
                        </div>
                    </div>

                    <?php if ($total_items > 0): ?>
                        <form method="post" enctype="multipart/form-data" id="checkoutForm">
                            <!-- Hidden inputs untuk quantity terbaru -->
                            <div id="quantityInputs"></div>
                            
                            <div class="mb-3">
                                <label for="bukti" class="form-label fw-semibold">
                                    <i class="fas fa-upload me-2"></i>Bukti Pembayaran *
                                </label>
                                <input type="file" 
                                       name="bukti" 
                                       id="bukti" 
                                       class="form-control form-control-custom" 
                                       accept="image/*" 
                                       required>
                                <small class="text-muted">
                                    Format: JPG, JPEG, PNG, GIF (Max: 5MB)
                                </small>
                                <div id="imagePreview"></div>
                                 <label for="bukti" class="form-label fw-semibold">
                                    <i class="fas fa-upload me-2"></i>Silahkan cek lokasi dulu dihalaman edit profil sebelum pesan 
                                </label>
                            </div>

                            <!-- Hidden inputs for selected items -->
                            <div id="selectedItemsInput"></div>

                            <button type="submit" 
                                    name="checkout" 
                                    class="btn btn-success-custom btn-custom w-100" 
                                    id="checkoutBtn" 
                                    disabled>
                                <i class="fas fa-shopping-bag me-2"></i>Pesan Sekarang
                            </button>
                        </form>

                        <!-- Payment Info -->
                        <div class="payment-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Informasi Pembayaran</h6>
                            <div class="small">
                                <strong>Transfer ke:</strong><br>
                                <?= htmlspecialchars($dataPembayaran['bank']) ?>: 
                                <?= htmlspecialchars($dataPembayaran['no_rek']) ?><br>
                                a.n. <?= htmlspecialchars($dataPembayaran['pemilik']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const totalAmountElement = document.getElementById('totalAmount');
    const subtotalAmountElement = document.getElementById('subtotalAmount');
    const selectedCountElement = document.getElementById('selectedCount');
    const selectedItemCountElement = document.getElementById('selectedItemCount');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const selectedItemsInput = document.getElementById('selectedItemsInput');

    // 🔄 Function untuk update total berdasarkan checkbox yang dipilih dan quantity terbaru
    function updateTotal() {
        let total = 0;
        let selectedCount = 0;

        // Clear previous hidden inputs
        if (selectedItemsInput) {
            selectedItemsInput.innerHTML = '';
        }

        itemCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const itemId = checkbox.dataset.itemId;
                
                // Ambil quantity terbaru dari input field
                const qtyInput = document.querySelector(`input[name="jumlah[${itemId}]"]`);
                const price = parseFloat(checkbox.dataset.price);
                const quantity = parseInt(qtyInput.value) || 1;
                
                // Hitung subtotal berdasarkan quantity terbaru
                const subtotal = price * quantity;
                total += subtotal;
                selectedCount++;

                // Update data attributes checkbox dengan nilai terbaru
                checkbox.dataset.quantity = quantity;
                checkbox.dataset.subtotal = subtotal;

                // Add hidden input untuk item yang dipilih
                if (selectedItemsInput) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'selected_items[]';
                    hiddenInput.value = itemId;
                    selectedItemsInput.appendChild(hiddenInput);
                }
            }
        });

        // Update display elements
        if (totalAmountElement) totalAmountElement.textContent = 'Rp ' + total.toLocaleString('id-ID');
        if (subtotalAmountElement) subtotalAmountElement.textContent = 'Rp ' + total.toLocaleString('id-ID');
        if (selectedCountElement) selectedCountElement.textContent = selectedCount + ' items';
        if (selectedItemCountElement) selectedItemCountElement.textContent = selectedCount;

        // Enable/disable checkout button
        if (checkoutBtn) {
            checkoutBtn.disabled = selectedCount === 0;
        }

        // Update select all checkbox state
        if (selectAllCheckbox && itemCheckboxes.length > 0) {
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < itemCheckboxes.length;
            selectAllCheckbox.checked = selectedCount === itemCheckboxes.length;
        }
    }

    // 📝 Select all functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateTotal();
        });
    }

    // 🔘 Individual checkbox change
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateTotal);
    });

    // 🔢 Quantity input change - update subtotal dan total langsung
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', function() {
            const itemId = this.dataset.itemId;
            updateSubtotalAndTotal(itemId);
        });
        
        input.addEventListener('change', function() {
            const itemId = this.dataset.itemId;
            updateSubtotalAndTotal(itemId);
        });
    });

    // Initial update
    updateTotal();

    // 📝 Form validation untuk checkout
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const selectedItems = document.querySelectorAll('.item-checkbox:checked');
            if (selectedItems.length === 0) {
                e.preventDefault();
                alert('Pilih minimal satu item untuk checkout!');
                return false;
            }

            const buktiFile = document.getElementById('bukti').files[0];
            if (!buktiFile) {
                e.preventDefault();
                alert('Bukti pembayaran wajib diupload!');
                return false;
            }

            // Validasi ukuran file (max 5MB)
            if (buktiFile.size > 5 * 1024 * 1024) {
                e.preventDefault();
                alert('Ukuran file terlalu besar! Maksimal 5MB.');
                return false;
            }

            // Konfirmasi dengan total yang BENAR dari display
            const totalText = document.getElementById('totalAmount').textContent;
            const selectedCount = selectedItems.length;
            const confirmMessage = `Lanjutkan checkout?\n\nItems: ${selectedCount}\nTotal: ${totalText}`;
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return false;
            }
        });
    }

    // 🖼️ Preview bukti pembayaran
    const buktiInput = document.getElementById('bukti');
    if (buktiInput) {
        buktiInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewContainer = document.getElementById('imagePreview');
            
            if (file && previewContainer) {
                // Clear previous preview
                previewContainer.innerHTML = '';

                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    img.alt = 'Preview Bukti Pembayaran';
                    
                    const fileName = document.createElement('small');
                    fileName.className = 'text-muted d-block mt-2';
                    fileName.textContent = file.name;
                    
                    previewContainer.appendChild(img);
                    previewContainer.appendChild(fileName);
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// 🔢 Function untuk menghitung total dari item yang dipilih dengan quantity terbaru
function calculateSelectedTotal() {
    let total = 0;
    const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
    
    selectedCheckboxes.forEach(checkbox => {
        const itemId = checkbox.dataset.itemId;
        const qtyInput = document.querySelector(`input[name="jumlah[${itemId}]"]`);
        const price = parseFloat(checkbox.dataset.price);
        const quantity = parseInt(qtyInput.value) || 1;
        
        total += price * quantity;
    });
    
    return total;
}

// 🔄 Update subtotal dan total - fungsi utama untuk sinkronisasi
function updateSubtotalAndTotal(itemId) {
    const qtyInput = document.getElementById(`qty_${itemId}`);
    const subtotalElement = document.getElementById(`subtotal_${itemId}`);
    const checkbox = document.querySelector(`input[value="${itemId}"]`);
    
    if (qtyInput && subtotalElement && checkbox) {
        const quantity = parseInt(qtyInput.value) || 1;
        const price = parseFloat(checkbox.dataset.price);
        const subtotal = quantity * price;
        
        // Update display subtotal untuk item ini
        subtotalElement.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        
        // Update data attributes di checkbox
        checkbox.dataset.quantity = quantity;
        checkbox.dataset.subtotal = subtotal;
        
        // Update total keseluruhan jika item ini dipilih
        updateTotalDisplay();
    }
}

// 🔄 Update tampilan total dan hidden inputs untuk checkout
function updateTotalDisplay() {
    let total = 0;
    let selectedCount = 0;
    
    // Clear previous hidden inputs
    const selectedItemsInput = document.getElementById('selectedItemsInput');
    const quantityInputs = document.getElementById('quantityInputs');
    
    if (selectedItemsInput) {
        selectedItemsInput.innerHTML = '';
    }
    if (quantityInputs) {
        quantityInputs.innerHTML = '';
    }

    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    itemCheckboxes.forEach(checkbox => {
        if (checkbox.checked) {
            const itemId = checkbox.dataset.itemId;
            
            // Ambil quantity terbaru dari input field
            const qtyInput = document.querySelector(`input[name="jumlah[${itemId}]"]`);
            const price = parseFloat(checkbox.dataset.price);
            const quantity = parseInt(qtyInput.value) || 1;
            
            // Hitung subtotal berdasarkan quantity terbaru
            const subtotal = price * quantity;
            total += subtotal;
            selectedCount++;

            // Add hidden input untuk item yang dipilih
            if (selectedItemsInput) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'selected_items[]';
                hiddenInput.value = itemId;
                selectedItemsInput.appendChild(hiddenInput);
            }
            
            // Add hidden input untuk quantity terbaru
            if (quantityInputs) {
                const qtyHiddenInput = document.createElement('input');
                qtyHiddenInput.type = 'hidden';
                qtyHiddenInput.name = `jumlah[${itemId}]`;
                qtyHiddenInput.value = quantity;
                quantityInputs.appendChild(qtyHiddenInput);
            }
        }
    });

    // Update display elements
    const totalAmountElement = document.getElementById('totalAmount');
    const subtotalAmountElement = document.getElementById('subtotalAmount');
    const selectedCountElement = document.getElementById('selectedCount');
    const selectedItemCountElement = document.getElementById('selectedItemCount');
    const checkoutBtn = document.getElementById('checkoutBtn');

    if (totalAmountElement) totalAmountElement.textContent = 'Rp ' + total.toLocaleString('id-ID');
    if (subtotalAmountElement) subtotalAmountElement.textContent = 'Rp ' + total.toLocaleString('id-ID');
    if (selectedCountElement) selectedCountElement.textContent = selectedCount + ' items';
    if (selectedItemCountElement) selectedItemCountElement.textContent = selectedCount;

    // Enable/disable checkout button
    if (checkoutBtn) {
        checkoutBtn.disabled = selectedCount === 0;
    }
}

// ➕ Increase quantity function
function increaseQuantity(itemId) {
    const qtyInput = document.getElementById(`qty_${itemId}`);
    const currentValue = parseInt(qtyInput.value) || 1;
    if (currentValue < 99) {
        qtyInput.value = currentValue + 1;
        updateSubtotalAndTotal(itemId);
    }
}

// ➖ Decrease quantity function
function decreaseQuantity(itemId) {
    const qtyInput = document.getElementById(`qty_${itemId}`);
    const currentValue = parseInt(qtyInput.value) || 1;
    if (currentValue > 1) {
        qtyInput.value = currentValue - 1;
        updateSubtotalAndTotal(itemId);
    }
}

// 🗑️ Confirm delete function
function confirmDelete(itemId) {
    if (confirm('Yakin ingin menghapus item ini dari keranjang?')) {
        window.location.href = `index.php?page=keranjang/keranjang&hapus=${itemId}`;
    }
}

// 🧹 Clear selected items function
function clearSelected() {
    const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        alert('Pilih item yang ingin dihapus terlebih dahulu!');
        return;
    }
    
    if (confirm(`Yakin ingin menghapus ${selectedCheckboxes.length} item yang dipilih?`)) {
        // Buat array ID untuk dihapus
        const itemsToDelete = Array.from(selectedCheckboxes).map(cb => cb.value);
        
        // Redirect untuk menghapus semua item sekaligus
        const deleteParams = itemsToDelete.map(id => `hapus[]=${id}`).join('&');
        window.location.href = `index.php?page=keranjang/keranjang&${deleteParams}`;
    }
}

// 💾 Auto-save quantity changes dengan debouncing - DIPERBAIKI
let saveTimeout;
document.addEventListener('input', function(e) {
    if (e.target.matches('.quantity-input')) {
        clearTimeout(saveTimeout);
        
        // Update subtotal immediately untuk UX yang lebih baik
        const itemId = e.target.dataset.itemId;
        updateSubtotalAndTotal(itemId);
        
        // Auto-save setelah 1 detik tidak ada perubahan
        saveTimeout = setTimeout(() => {
            const form = document.getElementById('cartForm');
            if (form) {
                const formData = new FormData(form);
                formData.append('update_jumlah', '1');
                
                // Update quantity ke database menggunakan AJAX
                fetch('index.php?page=keranjang/keranjang', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    if (response.ok) {
                        console.log('Quantity updated successfully to database');
                        showToast('Keranjang berhasil diupdate!', 'success');
                    }
                }).catch(error => {
                    console.error('Error updating quantity:', error);
                    showToast('Gagal mengupdate keranjang!', 'error');
                });
            }
        }, 1000); // Dipercepat jadi 1 detik
    }
});

// 🍞 Function untuk menampilkan toast notification
function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}
</script>
</body>
</html>