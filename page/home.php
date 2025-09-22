<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "config/koneksi.php";

// Set timezone Indonesia
date_default_timezone_set("Asia/Jakarta");

// 🔒 Cek login
$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
    exit;
}

// 🎯 Ambil username
$username = $_SESSION['username'] ?? null;
if (!$username) {
    $user_query = mysqli_query($koneksi, "SELECT username FROM users WHERE id_user = '$id_user'");
    $user_data = mysqli_fetch_assoc($user_query);
    $username = $user_data['username'] ?? null;
}
if (!$username) {
    echo "<script>alert('Username tidak ditemukan.'); window.location='login.php';</script>";
    exit;
}

// 🎯 Ambil id_pelanggan
$pelanggan_query = mysqli_query($koneksi, "SELECT id_pelanggan FROM pelanggan WHERE username = '$username'");
$pelanggan_data = mysqli_fetch_assoc($pelanggan_query);
$id_pelanggan = $pelanggan_data['id_pelanggan'] ?? null;
if (!$id_pelanggan) {
    echo "<script>alert('Data pelanggan tidak ditemukan untuk username: $username'); window.location='index.php';</script>";
    exit;
}

// 🎯 Ambil jam buka dan jam closing dari tabel data_cafe
$cafe_query = mysqli_query($koneksi, "SELECT jam_buka, jam_closing FROM data_cafe LIMIT 1");
$cafe_data = mysqli_fetch_assoc($cafe_query);

$jam_buka = $cafe_data['jam_buka'] ?? "08:00:00";
$jam_closing = $cafe_data['jam_closing'] ?? "22:00:00";

// 🎯 Waktu sekarang (Indonesia)
$waktu_sekarang = date("H:i:s");

// Status cafe
$status_cafe = "";

if ($jam_closing < $jam_buka) {
    if ($waktu_sekarang >= $jam_buka || $waktu_sekarang <= $jam_closing) {
        $status_cafe = "buka";
    } elseif ($waktu_sekarang < $jam_buka) {
        $status_cafe = "belum_buka";
    } else {
        $status_cafe = "tutup";
    }
} else {
    // Operasional normal
    if ($waktu_sekarang >= $jam_buka && $waktu_sekarang <= $jam_closing) {
        $status_cafe = "buka";
    } elseif ($waktu_sekarang < $jam_buka) {
        $status_cafe = "belum_buka";
    } else {
        $status_cafe = "tutup";
    }
}

$success_message = "";
$selected_item = "";

// 🎯 Hanya izinkan add_to_cart jika cafe buka
if ($status_cafe == "buka" && isset($_POST['add_to_cart'])) {
    $id_makanan = $_POST['id_makanan'] ?? null;
    $id_minuman = $_POST['id_minuman'] ?? null;
    $jumlah = 1;

    $check_query = "SELECT id, jumlah FROM keranjang WHERE id_pelanggan=? AND id_makanan=? AND id_minuman=?";
    $stmt = $koneksi->prepare($check_query);
    $stmt->bind_param("iii", $id_pelanggan, $id_makanan, $id_minuman);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $new_jumlah = $row['jumlah'] + $jumlah;
        $update_query = "UPDATE keranjang SET jumlah=? WHERE id=?";
        $update_stmt = $koneksi->prepare($update_query);
        $update_stmt->bind_param("ii", $new_jumlah, $row['id']);
        $update_stmt->execute();
    } else {
        $insert_query = "INSERT INTO keranjang (id_pelanggan, id_makanan, id_minuman, jumlah) VALUES (?, ?, ?, ?)";
        $insert_stmt = $koneksi->prepare($insert_query);
        $insert_stmt->bind_param("iiii", $id_pelanggan, $id_makanan, $id_minuman, $jumlah);
        $insert_stmt->execute();
    }
    
    $success_message = "Item berhasil ditambahkan ke keranjang!";
}

// 🎯 Ambil menu tanpa filter stok
$items = [];
if ($status_cafe == "buka") {
    $res_makanan = mysqli_query($koneksi, "SELECT id, nama, deskripsi, harga, stok, gambar, 'makanan' as tipe FROM makanan");
    while ($row = mysqli_fetch_assoc($res_makanan)) {
        $items[] = $row;
    }

    $res_minuman = mysqli_query($koneksi, "SELECT id, nama, deskripsi, harga, stok, gambar, 'minuman' as tipe FROM minuman");
    while ($row = mysqli_fetch_assoc($res_minuman)) {
        $items[] = $row;
    }
}

// Hitung jumlah item di keranjang
$cart_count_query = mysqli_query($koneksi, "SELECT SUM(jumlah) as total_items FROM keranjang WHERE id_pelanggan = $id_pelanggan");
$cart_count_data = mysqli_fetch_assoc($cart_count_query);
$cart_total_items = $cart_count_data['total_items'] ?? 0;

// Format waktu
function formatTime($time) {
    return date("H:i", strtotime($time));
}
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

    /* Alert Styles */
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

    .info-alert {
        background: linear-gradient(135deg, var(--info-color), #20c997);
        color: var(--white);
        padding: 1.5rem 2rem;
        border-radius: var(--border-radius-lg);
        margin-bottom: 2rem;
        text-align: center;
        font-weight: 500;
        box-shadow: var(--shadow-sm);
        border: none;
        font-size: 1.1rem;
    }

    .warning-alert {
        background: linear-gradient(135deg, var(--warning-color), #ffdb4d);
        color: var(--dark-color);
        padding: 1.5rem 2rem;
        border-radius: var(--border-radius-lg);
        margin-bottom: 2rem;
        text-align: center;
        font-weight: 500;
        box-shadow: var(--shadow-sm);
        border: none;
        font-size: 1.1rem;
    }

    .danger-alert {
        background: linear-gradient(135deg, var(--danger-color), #e74c3c);
        color: var(--white);
        padding: 1.5rem 2rem;
        border-radius: var(--border-radius-lg);
        margin-bottom: 2rem;
        text-align: center;
        font-weight: 500;
        box-shadow: var(--shadow-sm);
        border: none;
        font-size: 1.1rem;
    }

    .success-alert i,
    .info-alert i,
    .warning-alert i,
    .danger-alert i {
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
        transition: all 0.3s ease;
    }

    .menu-item:hover {
        box-shadow: var(--shadow);
        border-color: var(--secondary-color);
        transform: translateY(-2px);
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
        transition: transform 0.3s ease;
    }

    .menu-item:hover .item-image {
        transform: scale(1.05);
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
        transition: all 0.3s ease;
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
        transform: translateY(-1px);
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
        transform: translateY(-1px);
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
        transform: none;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .status-open {
        background: greenyellow;
        color: black;
    }

    .status-closed {
        background: greenyellow;
        color: black   
    }

    .status-pending {
        background: greenyellow;
        color: black   
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
            <h1><i class="fas fa-utensils header-icon"></i> Menu Cafe Kafka</h1>
            <p>Nikmati pilihan terbaik makanan dan minuman berkualitas tinggi</p>
            <!-- Status Badge -->
            <div class="mt-3">
                <?php if ($status_cafe == "buka"): ?>
                    <span class="status-badge status-open">
                        <i class="fas fa-circle"></i> Cafe Buka
                    </span>
                <?php elseif ($status_cafe == "belum_buka"): ?>
                    <span class="status-badge status-pending">
                        <i class="fas fa-clock"></i> Belum Buka
                    </span>
                <?php else: ?>
                    <span class="status-badge status-closed">
                        <i class="fas fa-door-closed"></i> Tutup
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="main-container">

    <?php if ($success_message): ?>
        <div class="success-alert">
            <i class="fas fa-check-circle"></i>
            <?= $success_message ?>
        </div>
    <?php endif; ?>

    <?php if ($status_cafe == "belum_buka"): ?>
        <div class="warning-alert">
            <i class="fas fa-clock"></i>
            Cafe belum buka. Jam buka: <?= formatTime($jam_buka) ?> 
            <?php if ($jam_closing < $jam_buka): ?>
                (sampai <?= formatTime($jam_closing) ?> hari berikutnya)
            <?php endif; ?>
        </div>
    <?php elseif ($status_cafe == "tutup"): ?>
        <div class="danger-alert">
            <i class="fas fa-door-closed"></i>
            Cafe sudah tutup. Jam buka kembali: <?= formatTime($jam_buka) ?>
            <?php if ($jam_closing < $jam_buka): ?>
                - <?= formatTime($jam_closing) ?> (besok)
            <?php else: ?>
                - <?= formatTime($jam_closing) ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="info-alert">
            <i class="fas fa-info-circle"></i>
            Cafe sedang buka! Jam operasional: <?= formatTime($jam_buka) ?> - <?= formatTime($jam_closing) ?>
            <?php if ($jam_closing < $jam_buka): ?>
                (lintas hari)
            <?php endif; ?>
        </div>

        <!-- Menu Items -->
        <?php if (empty($items)): ?>
            <div class="empty-menu">
                <i class="fas fa-coffee"></i>
                <h3>Menu Sedang Tidak Tersedia</h3>
                <p>Mohon maaf, saat ini belum ada menu yang dapat ditampilkan atau stok sedang habis.</p>
            </div>
        <?php else: ?>
            <div class="menu-container">
                <?php foreach($items as $item): ?>
                    <div class="menu-item">
                        <div class="item-image-wrapper">
                            <img src="<?= $item['gambar'] ? 'upload/' . $item['gambar'] : 'assets/default.jpg' ?>" 
                                 alt="<?= htmlspecialchars($item['nama']) ?>" class="item-image">
                            <div class="item-type-label <?= $item['tipe'] == 'makanan' ? 'label-makanan' : 'label-minuman' ?>">
                                <?= ucfirst($item['tipe']) ?>
                            </div>
                        </div>
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
                            <div class="item-actions">
                                <form method="post" style="flex:1;">
                                    <?php if($item['tipe'] == "makanan"): ?>
                                        <input type="hidden" name="id_makanan" value="<?= $item['id'] ?>">
                                    <?php else: ?>
                                        <input type="hidden" name="id_minuman" value="<?= $item['id'] ?>">
                                    <?php endif; ?>
                                    <button type="submit" name="add_to_cart" class="action-btn btn-add-cart">
                                        <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                                    </button>
                                </form>
                                <a href="index.php?page=pemesanan&tipe=<?= $item['tipe'] ?>&id=<?= $item['id'] ?>" 
                                   class="action-btn btn-order-now">
                                    <i class="fas fa-shopping-bag"></i> Pesan Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['debug'])): ?>
        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-top: 2rem; font-family: monospace;">
            <strong>Debug Info:</strong><br>
            Waktu Sekarang: <?= date("Y-m-d H:i:s") ?><br>
            Jam Buka: <?= $jam_buka ?><br>
            Jam Tutup: <?= $jam_closing ?><br>
            Status Cafe: <?= $status_cafe ?><br>
            Lintas Hari: <?= ($jam_closing < $jam_buka) ? 'Ya' : 'Tidak' ?>
        </div>
    <?php endif; ?>

</div>

<script>
// Auto refresh every 60 seconds to update cafe status
setTimeout(function() {
    window.location.reload();
}, 60000);

// Show notification when item added to cart
<?php if ($success_message): ?>
setTimeout(function() {
    document.querySelector('.success-alert').style.display = 'none';
}, 5000);
<?php endif; ?>
</script>

</body>
</html>