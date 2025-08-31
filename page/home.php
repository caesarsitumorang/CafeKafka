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
        --primary-color: #6f3f04ff;
        --secondary-color: #865803ff;
        --accent-color: #e74c3c;
        --success-color: #27ae60;
        --warning-color: #f39c12;
        --dark-color: #34495e;
        --light-color: #ecf0f1;
        --white: #ffffff;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-300: #dee2e6;
        --gray-600: #6c757d;
        --gray-800: #343a40;
        --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        --shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        --shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        --border-radius: 12px;
        --border-radius-lg: 16px;
        --border-radius-xl: 20px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, var(--gray-100) 0%, var(--light-color) 100%);
        min-height: 100vh;
        line-height: 1.6;
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: var(--white);
        padding: 2.5rem 0 3rem;
        margin-bottom: 2.5rem;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><pattern id="grain" width="100" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="0.5" fill="white" opacity="0.05"/><circle cx="30" cy="5" r="0.3" fill="white" opacity="0.05"/><circle cx="50" cy="15" r="0.4" fill="white" opacity="0.05"/><circle cx="70" cy="8" r="0.2" fill="white" opacity="0.05"/><circle cx="90" cy="12" r="0.3" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="20" fill="url(%23grain)"/></svg>');
    }

    .page-title {
        font-size: 2.5rem;
        font-weight: 600;
        text-align: center;
        margin: 0;
        position: relative;
        z-index: 2;
    }

    .page-subtitle {
        text-align: center;
        font-size: 1.1rem;
        margin-top: 0.5rem;
        opacity: 0.9;
        position: relative;
        z-index: 2;
        font-weight: 400;
    }

    .container-custom {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .menu-grid {
        display: grid;
        gap: 2rem;
        margin-bottom: 3rem;
    }

    /* Responsive Grid */
    @media (min-width: 1400px) {
        .menu-grid { grid-template-columns: repeat(6, 1fr); }
    }
    @media (min-width: 1200px) and (max-width: 1399px) {
        .menu-grid { grid-template-columns: repeat(5, 1fr); }
    }
    @media (min-width: 992px) and (max-width: 1199px) {
        .menu-grid { grid-template-columns: repeat(4, 1fr); }
    }
    @media (min-width: 768px) and (max-width: 991px) {
        .menu-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (min-width: 576px) and (max-width: 767px) {
        .menu-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 575px) {
        .menu-grid { grid-template-columns: 1fr; }
        .container-custom { padding: 0 1rem; }
    }

    .menu-card {
        background: var(--white);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
        border: 1px solid var(--gray-200);
    }

    .menu-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow);
        border-color: var(--secondary-color);
    }

    .card-image-container {
        position: relative;
        overflow: hidden;
        height: 200px;
        background: var(--gray-200);
    }

    .card-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .menu-card:hover .card-image {
        transform: scale(1.05);
    }

    .item-type-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        z-index: 10;
    }

    .badge-makanan {
        background: linear-gradient(135deg, var(--accent-color), #ff7675);
        color: var(--white);
    }

    .badge-minuman {
        background: linear-gradient(135deg, var(--secondary-color), #74b9ff);
        color: var(--white);
    }

    .card-body-custom {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        justify-content: space-between;
    }

    .card-content {
        margin-bottom: 1.5rem;
    }

    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .card-description {
        font-size: 0.9rem;
        color: var(--gray-600);
        margin-bottom: 1rem;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .card-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .price-tag {
        background: linear-gradient(135deg, var(--success-color), #58d68d);
        color: var(--white);
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .stock-info {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        color: var(--gray-600);
        font-size: 0.85rem;
        font-weight: 500;
    }

    .stock-available {
        color: var(--success-color);
    }

    .stock-low {
        color: var(--warning-color);
    }

    .stock-empty {
        color: var(--accent-color);
    }

    .card-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-custom {
        padding: 0.7rem 1rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        font-size: 0.9rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        flex: 1;
        min-width: 110px;
        text-decoration: none;
    }

    .btn-cart {
        background: linear-gradient(135deg, var(--warning-color), #fdcb6e);
        color: var(--white);
        box-shadow: var(--shadow-sm);
    }

    .btn-cart:hover {
        background: linear-gradient(135deg, #e67e22, var(--warning-color));
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
        color: var(--white);
    }

    .btn-order {
        background: linear-gradient(135deg, var(--secondary-color), #74b9ff);
        color: var(--white);
        box-shadow: var(--shadow-sm);
    }

    .btn-order:hover {
        background: linear-gradient(135deg, #2980b9, var(--secondary-color));
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
        color: var(--white);
        text-decoration: none;
    }

    .btn-disabled {
        background: var(--gray-300);
        color: var(--gray-600);
        cursor: not-allowed;
        opacity: 0.6;
    }

    .btn-disabled:hover {
        background: var(--gray-300);
        color: var(--gray-600);
        transform: none;
        box-shadow: var(--shadow-sm);
    }

    /* Modal Styles */
    .modal-content {
        border-radius: var(--border-radius-lg);
        border: none;
        box-shadow: var(--shadow-lg);
        max-width: 400px;
        margin: 0 auto;
    }

    .modal-dialog {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 60px);
        margin: 30px auto;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--success-color), #58d68d);
        color: var(--white);
        border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
        border-bottom: none;
        padding: 1.2rem 1.5rem;
        position: relative;
    }

    .modal-title {
        font-weight: 600;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-body {
        padding: 1.5rem;
        text-align: center;
    }

    .modal-body p {
        font-size: 1rem;
        color: var(--gray-600);
        margin-bottom: 0.8rem;
    }

    .modal-body h6 {
        color: var(--primary-color);
        font-weight: 600;
        font-size: 1.1rem;
    }

    .btn-close {
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        width: 32px;
        height: 32px;
        opacity: 0.9;
        filter: none;
        font-size: 0.8rem;
    }

    .btn-close:hover {
        background: rgba(255,255,255,0.3);
        opacity: 1;
        transform: scale(1.1);
    }

    .modal-footer-custom {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--gray-200);
        display: flex;
        justify-content: center;
    }

    .btn-modal-close {
        background: var(--secondary-color);
        color: var(--white);
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-modal-close:hover {
        background: var(--primary-color);
        transform: translateY(-1px);
    }

    /* Loading State */
    .loading-skeleton {
        background: linear-gradient(90deg, var(--gray-200) 25%, var(--gray-300) 50%, var(--gray-200) 75%);
        background-size: 200% 100%;
        border-radius: var(--border-radius);
        height: 1rem;
        margin-bottom: 0.5rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--gray-600);
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h3 {
        margin-bottom: 1rem;
        color: var(--primary-color);
    }

    @media (max-width: 575px) {
        .page-header {
            padding: 2rem 0 3rem;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
        }

        .page-subtitle {
            font-size: 1rem;
        }

        .card-actions {
            flex-direction: column;
        }

        .btn-custom {
            min-width: auto;
        }

        .card-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
    }
  </style>
</head>
<body>

<!-- Page Header -->
<div class="page-header">
    <div class="container-custom">
        <h1 class="page-title">
            <i class="fas fa-utensils me-3"></i>
            Menu Cafe Kafka
        </h1>
        <p class="page-subtitle">Pilihan terbaik makanan dan minuman untuk Anda</p>
    </div>
</div>

<!-- Main Content -->
<div class="container-custom">
    <?php if (empty($items)): ?>
        <div class="empty-state">
            <i class="fas fa-coffee"></i>
            <h3>Menu Belum Tersedia</h3>
            <p>Mohon maaf, saat ini belum ada menu yang tersedia.</p>
        </div>
    <?php else: ?>
        <div class="menu-grid">
            <?php foreach($items as $row) { ?>
                <div class="menu-card">
                    <!-- Type Badge -->
                    <div class="item-type-badge <?= $row['tipe'] == 'makanan' ? 'badge-makanan' : 'badge-minuman' ?>">
                        <i class="fas <?= $row['tipe'] == 'makanan' ? 'fa-hamburger' : 'fa-coffee' ?>"></i>
                        <?= ucfirst($row['tipe']) ?>
                    </div>

                    <!-- Image -->
                    <div class="card-image-container">
                        <img src="<?= $row['gambar'] ? 'upload/' . $row['gambar'] : 'assets/default.jpg' ?>" 
                        alt="<?= htmlspecialchars($row['nama']) ?>" 
                        class="card-image">
                    </div>

                    <!-- Card Body -->
                    <div class="card-body-custom">
                        <div class="card-content">
                            <h5 class="card-title"><?= htmlspecialchars($row['nama']) ?></h5>
                            <p class="card-description"><?= htmlspecialchars($row['deskripsi']) ?></p>
                            
                            <div class="card-info">
                                <div class="price-tag">
                                    <i class="fas fa-tag"></i>
                                    Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                                </div>
                                <div class="stock-info <?= $row['stok'] > 10 ? 'stock-available' : ($row['stok'] > 0 ? 'stock-low' : 'stock-empty') ?>">
                                    <i class="fas fa-boxes"></i>
                                    Stok: <?= $row['stok'] ?>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="card-actions">
                            <?php if ($row['stok'] > 0): ?>
                                <form method="post" style="flex: 1;">
                                    <?php if($row['tipe'] == "makanan") { ?>
                                        <input type="hidden" name="id_makanan" value="<?= $row['id'] ?>">
                                    <?php } else { ?>
                                        <input type="hidden" name="id_minuman" value="<?= $row['id'] ?>">
                                    <?php } ?>
                                    <button type="submit" name="add_to_cart" class="btn-custom btn-cart w-100">
                                        <i class="fas fa-shopping-cart"></i>
                                        Keranjang
                                    </button>
                                </form>
                                <a href="index.php?page=pemesanan&tipe=<?= $row['tipe'] ?>&id=<?= $row['id'] ?>" 
                                   class="btn-custom btn-order" style="flex: 1;">
                                    <i class="fas fa-shopping-bag"></i>
                                    Pesan
                                </a>
                            <?php else: ?>
                                <button class="btn-custom btn-disabled w-100" disabled>
                                    <i class="fas fa-times-circle"></i>
                                    Stok Habis
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php endif; ?>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">
                    <i class="fas fa-check-circle"></i>
                    Berhasil Ditambahkan!
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><?= $success_message ?></p>
                <?php if ($selected_item): ?>
                    <h6><strong><?= htmlspecialchars($selected_item) ?></strong></h6>
                <?php endif; ?>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn-modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i>
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<?php if($success_message) { ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    });
</script>
<?php } ?>

</body>
</html>