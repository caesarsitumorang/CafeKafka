<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "config/koneksi.php";

// 🔒 Cek login
$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
    exit;
}

// 🎯 PERBAIKAN: Ambil username dari session atau tabel users
$username = $_SESSION['username'] ?? null;

// Jika username tidak ada di session, ambil dari database users
if (!$username) {
    $user_query = mysqli_query($koneksi, "SELECT username FROM users WHERE id_user = '$id_user'");
    $user_data = mysqli_fetch_assoc($user_query);
    $username = $user_data['username'] ?? null;
}

if (!$username) {
    echo "<script>alert('Username tidak ditemukan.'); window.location='login.php';</script>";
    exit;
}

// 🎯 Ambil id_pelanggan berdasarkan username yang sama
$pelanggan_query = mysqli_query($koneksi, "SELECT id_pelanggan FROM pelanggan WHERE username = '$username'");
$pelanggan_data = mysqli_fetch_assoc($pelanggan_query);
$id_pelanggan = $pelanggan_data['id_pelanggan'] ?? null;

if (!$id_pelanggan) {
    echo "<script>alert('Data pelanggan tidak ditemukan untuk username: $username'); window.location='index.php';</script>";
    exit;
}

$success_message = "";
$selected_item = "";

if (isset($_POST['add_to_cart'])) {
    $id_makanan = $_POST['id_makanan'] ?? null;
    $id_minuman = $_POST['id_minuman'] ?? null;

    // Tentukan jumlah awal
    $jumlah = 1;

    // 🔍 PERBAIKAN: Cek apakah item sudah ada di keranjang untuk id_pelanggan yang benar
    $check_query = "SELECT id, jumlah FROM keranjang WHERE id_pelanggan=? AND id_makanan=? AND id_minuman=?";
    $stmt = $koneksi->prepare($check_query);
    $stmt->bind_param("iii", $id_pelanggan, $id_makanan, $id_minuman); // Menggunakan id_pelanggan bukan id_user
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Jika ada, update jumlah
        $row = $result->fetch_assoc();
        $new_jumlah = $row['jumlah'] + $jumlah;
        $update_query = "UPDATE keranjang SET jumlah=? WHERE id=?";
        $update_stmt = $koneksi->prepare($update_query);
        $update_stmt->bind_param("ii", $new_jumlah, $row['id']);
        
        if ($update_stmt->execute()) {
            $success_message = "Item berhasil ditambahkan ke keranjang (jumlah diperbarui)!";
        } else {
            $error_message = "Gagal memperbarui keranjang!";
        }
    } else {
        // 🆕 PERBAIKAN: Insert data baru dengan id_pelanggan yang benar
        $insert_query = "INSERT INTO keranjang (id_pelanggan, id_makanan, id_minuman, jumlah) VALUES (?, ?, ?, ?)";
        $insert_stmt = $koneksi->prepare($insert_query);
        $insert_stmt->bind_param("iiii", $id_pelanggan, $id_makanan, $id_minuman, $jumlah); // Menggunakan id_pelanggan
        
        if ($insert_stmt->execute()) {
            $success_message = "Item berhasil ditambahkan ke keranjang!";
        } else {
            $error_message = "Gagal menambahkan item ke keranjang!";
        }
    }

    // Ambil nama item untuk feedback
    if ($id_makanan) {
        $res = mysqli_query($koneksi, "SELECT nama FROM makanan WHERE id=$id_makanan");
        $row = mysqli_fetch_assoc($res);
        $selected_item = $row['nama'] ?? 'Item';
    } elseif ($id_minuman) {
        $res = mysqli_query($koneksi, "SELECT nama FROM minuman WHERE id=$id_minuman");
        $row = mysqli_fetch_assoc($res);
        $selected_item = $row['nama'] ?? 'Item';
    }

    // Update success message dengan nama item
    if ($success_message && $selected_item) {
        $success_message = "$selected_item berhasil ditambahkan ke keranjang!";
    }
}

// Ambil data items (makanan dan minuman)
$items = [];

$res_makanan = mysqli_query($koneksi, "SELECT id, nama, deskripsi, harga, stok, gambar, 'makanan' as tipe FROM makanan WHERE stok > 0");
while ($row = mysqli_fetch_assoc($res_makanan)) {
    $items[] = $row;
}

$res_minuman = mysqli_query($koneksi, "SELECT id, nama, deskripsi, harga, stok, gambar, 'minuman' as tipe FROM minuman WHERE stok > 0");
while ($row = mysqli_fetch_assoc($res_minuman)) {
    $items[] = $row;
}

// Hitung jumlah item di keranjang untuk badge
$cart_count_query = mysqli_query($koneksi, "SELECT SUM(jumlah) as total_items FROM keranjang WHERE id_pelanggan = $id_pelanggan");
$cart_count_data = mysqli_fetch_assoc($cart_count_query);
$cart_total_items = $cart_count_data['total_items'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cafe Kafka - Menu</title>
  <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    :root {
        --primary-color: #8B4513;
        --secondary-color: #D2B48C;
        --accent-color: #CD853F;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --info-color: #17a2b8;
        --dark-color: #343a40;
        --light-bg: #f8f9fa;
        --white: #ffffff;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-300: #dee2e6;
        --gray-400: #ced4da;
        --gray-500: #adb5bd;
        --gray-600: #6c757d;
        --gray-700: #495057;
        --gray-800: #343a40;
        --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        --shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        --border-radius: 8px;
        --border-radius-lg: 12px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--light-bg);
        color: var(--dark-color);
        line-height: 1.6;
    }

    /* Header Section */
    .header-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
        color: var(--white);
        padding: 3rem 0;
        text-align: center;
        margin-bottom: 2.5rem;
        box-shadow: var(--shadow);
    }

    .header-content h1 {
        font-size: 2.75rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        letter-spacing: -0.02em;
    }

    .header-content p {
        font-size: 1.2rem;
        opacity: 0.95;
        font-weight: 400;
        max-width: 600px;
        margin: 0 auto;
    }

    .header-icon {
        font-size: 1.5rem;
        margin-right: 0.75rem;
        vertical-align: middle;
    }

    /* Main Container */
    .main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    /* Success Alert */
    .success-alert {
        background: linear-gradient(135deg, var(--success-color), #34ce57);
        color: var(--white);
        padding: 1rem 1.5rem;
        border-radius: var(--border-radius-lg);
        margin-bottom: 2rem;
        text-align: center;
        font-weight: 500;
        box-shadow: var(--shadow-sm);
        border: none;
    }

    .success-alert i {
        margin-right: 0.5rem;
        font-size: 1.1rem;
    }

    /* Menu Grid */
    .menu-container {
        display: grid;
        gap: 2rem;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        margin-bottom: 3rem;
    }

    /* Individual Menu Card */
    .menu-item {
        background: var(--white);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .menu-item:hover {
        box-shadow: var(--shadow);
        border-color: var(--secondary-color);
    }

    /* Image Section */
    .item-image-wrapper {
        position: relative;
        height: 220px;
        overflow: hidden;
        background: var(--gray-200);
    }

    .item-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item-type-label {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.5rem 0.75rem;
        border-radius: var(--border-radius);
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        backdrop-filter: blur(10px);
        color: var(--white);
    }

    .label-makanan {
        background: rgba(220, 53, 69, 0.9);
    }

    .label-minuman {
        background: rgba(23, 162, 184, 0.9);
    }

    /* Content Section */
    .item-content {
        padding: 1.5rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .item-details {
        flex-grow: 1;
        margin-bottom: 1.5rem;
    }

    .item-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }

    .item-description {
        font-size: 0.95rem;
        color: var(--gray-600);
        margin-bottom: 1.25rem;
        line-height: 1.5;
    }

    /* Info Row */
    .item-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        gap: 1rem;
    }

    .price-display {
        background: linear-gradient(135deg, var(--success-color), #34ce57);
        color: var(--white);
        padding: 0.6rem 1rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        white-space: nowrap;
    }

    .stock-display {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.9rem;
        font-weight: 500;
        padding: 0.4rem 0.75rem;
        border-radius: var(--border-radius);
    }

    .stock-high {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }

    .stock-medium {
        background: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
    }

    .stock-low {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger-color);
    }

    /* Action Buttons */
    .item-actions {
        display: flex;
        gap: 0.75rem;
    }

    .action-btn {
        flex: 1;
        padding: 0.75rem 1rem;
        border: none;
        border-radius: var(--border-radius);
        font-weight: 500;
        font-size: 0.95rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        text-decoration: none;
        min-height: 44px;
    }

    .btn-add-cart {
        background: linear-gradient(135deg, var(--warning-color), #ffdb4d);
        color: var(--dark-color);
        box-shadow: var(--shadow-sm);
    }

    .btn-add-cart:hover {
        background: linear-gradient(135deg, #e0a800, var(--warning-color));
        color: var(--dark-color);
        text-decoration: none;
    }

    .btn-order-now {
        background: linear-gradient(135deg, var(--info-color), #20c997);
        color: var(--white);
        box-shadow: var(--shadow-sm);
    }

    .btn-order-now:hover {
        background: linear-gradient(135deg, #138496, var(--info-color));
        color: var(--white);
        text-decoration: none;
    }

    .btn-unavailable {
        background: var(--gray-400);
        color: var(--gray-600);
        cursor: not-allowed;
        opacity: 0.7;
    }

    .btn-unavailable:hover {
        background: var(--gray-400);
        color: var(--gray-600);
    }

    /* Empty State */
    .empty-menu {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--gray-600);
    }

    .empty-menu i {
        font-size: 4rem;
        margin-bottom: 1.5rem;
        color: var(--gray-400);
    }

    .empty-menu h3 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        color: var(--primary-color);
    }

    .empty-menu p {
        font-size: 1rem;
        max-width: 400px;
        margin: 0 auto;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .menu-container {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
    }

    @media (max-width: 768px) {
        .main-container {
            padding: 0 1rem;
        }
        
        .header-section {
            padding: 2rem 0;
        }

        .header-content h1 {
            font-size: 2.25rem;
        }

        .header-content p {
            font-size: 1rem;
        }

        .menu-container {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .item-info-row {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }

        .item-actions {
            flex-direction: column;
            gap: 0.5rem;
        }
    }

    @media (max-width: 480px) {
        .header-content h1 {
            font-size: 1.875rem;
        }
        
        .item-content {
            padding: 1.25rem;
        }

        .item-image-wrapper {
            height: 180px;
        }
    }
  </style>
</head>
<body>

<!-- Header Section -->
<div class="header-section">
    <div class="main-container">
        <div class="header-content">
            <h1>
                <i class="fas fa-utensils header-icon"></i>
                Menu Cafe Kafka
            </h1>
            <p>Nikmati pilihan terbaik makanan dan minuman berkualitas tinggi</p>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="main-container">
    <!-- Success Message -->
    <?php if ($success_message): ?>
        <div class="success-alert">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>

    <!-- Menu Items -->
    <?php if (empty($items)): ?>
        <div class="empty-menu">
            <i class="fas fa-coffee"></i>
            <h3>Menu Sedang Tidak Tersedia</h3>
            <p>Mohon maaf, saat ini belum ada menu yang dapat ditampilkan. Silakan coba lagi nanti.</p>
        </div>
    <?php else: ?>
        <div class="menu-container">
            <?php foreach($items as $item): ?>
                <div class="menu-item">
                    <!-- Image Section -->
                    <div class="item-image-wrapper">
                        <img src="<?= $item['gambar'] ? 'upload/' . $item['gambar'] : 'assets/default.jpg' ?>" 
                             alt="<?= htmlspecialchars($item['nama']) ?>" 
                             class="item-image">
                        <div class="item-type-label <?= $item['tipe'] == 'makanan' ? 'label-makanan' : 'label-minuman' ?>">
                            <?= ucfirst($item['tipe']) ?>
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="item-content">
                        <div class="item-details">
                            <h3 class="item-title"><?= htmlspecialchars($item['nama']) ?></h3>
                            <p class="item-description"><?= htmlspecialchars($item['deskripsi']) ?></p>
                            
                            <div class="item-info-row">
                                <div class="price-display">
                                    <i class="fas fa-tag"></i>
                                    Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                                </div>
                                <div class="stock-display <?= $item['stok'] > 10 ? 'stock-high' : ($item['stok'] > 5 ? 'stock-medium' : 'stock-low') ?>">
                                    <i class="fas fa-cube"></i>
                                    Stok: <?= $item['stok'] ?>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="item-actions">
                            <?php if ($item['stok'] > 0): ?>
                                <form method="post" style="flex: 1;">
                                    <?php if($item['tipe'] == "makanan"): ?>
                                        <input type="hidden" name="id_makanan" value="<?= $item['id'] ?>">
                                    <?php else: ?>
                                        <input type="hidden" name="id_minuman" value="<?= $item['id'] ?>">
                                    <?php endif; ?>
                                    <button type="submit" name="add_to_cart" class="action-btn btn-add-cart">
                                        <i class="fas fa-shopping-cart"></i>
                                        Tambah ke Keranjang
                                    </button>
                                </form>
                                <a href="index.php?page=pemesanan&tipe=<?= $item['tipe'] ?>&id=<?= $item['id'] ?>" 
                                   class="action-btn btn-order-now">
                                    <i class="fas fa-shopping-bag"></i>
                                    Pesan Sekarang
                                </a>
                            <?php else: ?>
                                <button class="action-btn btn-unavailable" disabled>
                                    <i class="fas fa-times-circle"></i>
                                    Stok Habis
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>