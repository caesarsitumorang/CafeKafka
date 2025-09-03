<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "config/koneksi.php";

// Cek login
$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
    exit;
}

// Ambil data user
$user_query = mysqli_query($koneksi, "SELECT username FROM users WHERE id_user = '$id_user'");
$user_data  = mysqli_fetch_assoc($user_query);

if (!$user_data) {
    echo "<script>alert('Data user tidak ditemukan.'); window.location='login.php';</script>";
    exit;
}

$username = $user_data['username'];

// Cari pelanggan berdasarkan username
$pelanggan_query = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE username = '$username'");
$pelanggan = mysqli_fetch_assoc($pelanggan_query);

if (!$pelanggan) {
    echo "<script>alert('Data pelanggan tidak ditemukan.'); window.location='login.php';</script>";
    exit;
}

$id_pelanggan = $pelanggan['id_pelanggan'];

// Ambil parameter URL
$tipe = $_GET['tipe'] ?? null;
$id   = $_GET['id'] ?? null;
if (!$tipe || !$id) {
    echo "<script>alert('Data tidak valid!'); window.location='index.php?page=home';</script>";
    exit;
}

// Ambil item
$table = ($tipe == 'makanan') ? 'makanan' : 'minuman';
$item = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM $table WHERE id=$id"));
if (!$item) {
    echo "<script>alert('Item tidak ditemukan!'); window.location='index.php?page=home';</script>";
    exit;
}

// Ambil data cafe
$cafe = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT bank, no_rek FROM data_cafe LIMIT 1"));

// Proses pesanan
$success_message = "";
if (isset($_POST['pesan'])) {
    $jumlah = intval($_POST['jumlah']);
    $total_harga = $item['harga'] * $jumlah;

    // Upload bukti
    $bukti = null;
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
        $ext = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
        $bukti = "upload/bukti_" . time() . "." . $ext;
        move_uploaded_file($_FILES['bukti']['tmp_name'], $bukti);
    }

    $id_makanan = ($tipe == "makanan") ? $item['id'] : null;
    $id_minuman = ($tipe == "minuman") ? $item['id'] : null;

    // Insert ke tabel pesanan
    $stmt = $koneksi->prepare("INSERT INTO pesanan (id_pelanggan, id_makanan, id_minuman, total_harga, status, bukti_pembayaran) VALUES (?, ?, ?, ?, 'pending', ?)");
    $stmt->bind_param("iiiis", $id_pelanggan, $id_makanan, $id_minuman, $total_harga, $bukti);
    $stmt->execute();

    // Ambil id_pesanan terakhir
    $id_pesanan = $koneksi->insert_id;

    // Insert ke pesanan_detail
    $stmt_detail = $koneksi->prepare("INSERT INTO pesanan_detail (id_pesanan, id_makanan, id_minuman, jumlah) VALUES (?, ?, ?, ?)");
    $stmt_detail->bind_param("iiii", $id_pesanan, $id_makanan, $id_minuman, $jumlah);
    $stmt_detail->execute();

    $success_message = "Pesanan berhasil dibuat!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pemesanan - <?= $item['nama'] ?></title>
<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
:root {
    --primary-color: #1a365d;
    --primary-light: #2d4a63;
    --secondary-color: #3182ce;
    --accent-color: #e53e3e;
    --success-color: #38a169;
    --warning-color: #d69e2e;
    --info-color: #3182ce;
    --dark-color: #2d3748;
    --light-color: #f7fafc;
    --white: #ffffff;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    --border-radius: 8px;
    --border-radius-lg: 12px;
    --border-radius-xl: 16px;
    --border-radius-2xl: 24px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: white;
    min-height: 100vh;
    line-height: 1.6;
    color: var(--gray-700);
}

.container {
    padding: 1rem;
    max-width: 1000px;
    margin: 0 auto;
}

/* Header Section */
.page-header {
    text-align: center;
    margin-bottom: 2rem;
    color: white;
}

.page-title {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.page-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    font-weight: 400;
}

/* Main Card */
.main-card {
    background: var(--white);
    border-radius: var(--border-radius-2xl);
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    border: 1px solid var(--gray-200);
}

.card-body {
    padding: 1.5rem;
}

/* Product Section */
.product-section {
    display: grid;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.product-image-container {
    position: relative;
    background: var(--gray-100);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    height: 200px;
    box-shadow: var(--shadow);
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 0.3s ease;
}

.product-image:hover {
    transform: scale(1.05);
}

.product-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: linear-gradient(135deg, var(--secondary-color), #4299e1);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
    box-shadow: var(--shadow);
}

.product-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.product-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1.3;
    margin-bottom: 0.5rem;
}

.product-description {
    color: var(--gray-600);
    font-size: 0.95rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.product-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.75rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: var(--gray-50);
    border-radius: var(--border-radius);
    border: 1px solid var(--gray-200);
}

.stat-icon {
    color: var(--secondary-color);
    font-size: 1.1rem;
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    margin-bottom: 2px;
}

.stat-value {
    font-weight: 700;
    color: var(--gray-800);
    font-size: 0.9rem;
}

/* Payment Info */
.payment-section {
    background: linear-gradient(135deg, var(--info-color), #4299e1);
    padding: 1.5rem;
    border-radius: var(--border-radius-lg);
    margin-bottom: 2rem;
    color: white;
    position: relative;
    overflow: hidden;
}

.payment-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.05"><path d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/></g></g></svg>') repeat;
    pointer-events: none;
}

.payment-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
}

.payment-title {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0;
}

.payment-details {
    display: grid;
    gap: 0.75rem;
    position: relative;
    z-index: 1;
}

.payment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.15);
    border-radius: var(--border-radius);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.payment-label {
    font-weight: 500;
    opacity: 0.9;
}

.payment-value {
    font-weight: 700;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

/* Form Section */
.form-section {
    display: grid;
    gap: 1.5rem;
}

.form-group {
    display: grid;
    gap: 0.5rem;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: var(--primary-color);
    font-size: 0.95rem;
}

.form-control {
    border: 2px solid var(--gray-200);
    border-radius: var(--border-radius);
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: var(--white);
}

.form-control:focus {
    outline: none;
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
}

.total-display {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--success-color);
    padding: 1rem;
    background: linear-gradient(135deg, #f0fff4, #c6f6d5);
    border-radius: var(--border-radius);
    border: 2px solid var(--success-color);
    text-align: center;
}

/* File Upload */
.file-input-wrapper {
    position: relative;
}

.file-input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
    z-index: 2;
}

.file-input-display {
    border: 2px dashed var(--gray-300);
    border-radius: var(--border-radius-lg);
    padding: 2rem;
    text-align: center;
    background: var(--gray-50);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.file-input-display:hover {
    border-color: var(--secondary-color);
    background: rgba(49, 130, 206, 0.05);
}

.file-input-content {
    color: var(--gray-600);
}

.preview-image {
    max-width: 100%;
    max-height: 150px;
    margin-top: 1rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    display: none;
}

/* Buttons */
.button-group {
    display: grid;
    gap: 1rem;
    margin-top: 1rem;
}

.btn-custom {
    padding: 1rem 1.5rem;
    border-radius: var(--border-radius-lg);
    font-weight: 600;
    font-size: 1rem;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    text-decoration: none;
    text-align: center;
}

.btn-primary {
    background: linear-gradient(135deg, var(--secondary-color), #4299e1);
    color: white;
    box-shadow: var(--shadow);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2c5282, var(--secondary-color));
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    color: white;
}

.btn-outline {
    background: white;
    color: var(--primary-color);
    border: 2px solid var(--gray-300);
}

.btn-outline:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    text-decoration: none;
}

/* Success Alert */
.alert-success {
    background: linear-gradient(135deg, #f0fff4, #c6f6d5);
    color: var(--success-color);
    border: 1px solid #9ae6b4;
    border-radius: var(--border-radius-lg);
    padding: 1rem 1.5rem;
    margin-top: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: var(--shadow);
}

/* Responsive Design */
@media (min-width: 768px) {
    .container {
        padding: 2rem;
    }
    
    .card-body {
        padding: 2rem;
    }
    
    .product-section {
        grid-template-columns: 300px 1fr;
        gap: 2rem;
    }
    
    .product-image-container {
        height: 250px;
    }
    
    .button-group {
        grid-template-columns: 1fr auto;
        align-items: center;
    }
    
    .page-title {
        font-size: 2.5rem;
    }
}

@media (min-width: 1024px) {
    .product-section {
        grid-template-columns: 350px 1fr;
    }
    
    .product-image-container {
        height: 280px;
    }
    
    .form-section {
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    
    .form-group:nth-child(3),
    .form-group:nth-child(4) {
        grid-column: 1 / -1;
    }
    
    .button-group {
        grid-column: 1 / -1;
        margin-top: 2rem;
    }
}

@media (max-width: 640px) {
    .product-stats {
        grid-template-columns: 1fr;
    }
    
    .payment-item {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
    
    .payment-value {
        width: 100%;
    }
}
</style>
</head>
<body>

<div class="container">
    <!-- Page Header -->


    <!-- Main Card -->
    <div class="main-card">
        <div class="card-body">
            <!-- Product Section -->
            <div class="product-section">
                <div class="product-image-container">
                    <?php 
                        $gambar = $item['gambar'] ?? ''; 
                        $gambar = str_replace("upload/", "", $gambar);
                        $path = $gambar ? "../upload/" . $gambar : "assets/default.jpg";
                    ?>
                    <img src="<?= $path ?>" 
                         alt="<?= htmlspecialchars($item['nama']) ?>" 
                         class="product-image">
                    <div class="product-badge">
                        <?= ucfirst($tipe) ?>
                    </div>
                </div>

                <div class="product-info">
                    <h1 class="product-title"><?= htmlspecialchars($item['nama']) ?></h1>
                    <p class="product-description"><?= htmlspecialchars($item['deskripsi']) ?></p>
                    
                    <div class="product-stats">
                        <div class="stat-item">
                            <i class="fas fa-tag stat-icon"></i>
                            <div class="stat-content">
                                <div class="stat-label">Harga</div>
                                <div class="stat-value">Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-boxes stat-icon"></i>
                            <div class="stat-content">
                                <div class="stat-label">Stok</div>
                                <div class="stat-value"><?= $item['stok'] ?> tersedia</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="payment-section">
                <div class="payment-header">
                    <i class="fas fa-credit-card"></i>
                    <h3 class="payment-title">Informasi Pembayaran</h3>
                </div>
                <div class="payment-details">
                    <div class="payment-item">
                        <span class="payment-label">Bank:</span>
                        <span class="payment-value"><?= htmlspecialchars($cafe['bank']) ?></span>
                    </div>
                    <div class="payment-item">
                        <span class="payment-label">No. Rekening:</span>
                        <span class="payment-value"><?= htmlspecialchars($cafe['no_rek']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Form Section -->
            <form method="post" enctype="multipart/form-data">
                <div class="form-section">
                    <div class="form-group">
                        <label for="jumlah" class="form-label">
                            <i class="fas fa-calculator"></i>
                            Jumlah Pesanan
                        </label>
                        <input type="number" 
                               name="jumlah" 
                               id="jumlah" 
                               class="form-control" 
                               min="1" 
                               max="<?= $item['stok'] ?>" 
                               value="1" 
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-money-bill-wave"></i>
                            Total Harga
                        </label>
                        <div id="total-harga" class="total-display">
                            Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="bukti" class="form-label">
                            <i class="fas fa-receipt"></i>
                            Upload Bukti Pembayaran
                        </label>
                        <div class="file-input-wrapper">
                            <input type="file" 
                                   name="bukti" 
                                   id="bukti" 
                                   class="file-input" 
                                   accept="image/*" 
                                   onchange="previewBukti(event)" 
                                   required>
                            <div class="file-input-display">
                                <div class="file-input-content">
                                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                    <div><strong>Klik untuk upload gambar</strong></div>
                                    <small>Format: JPG, PNG, GIF (Max: 5MB)</small>
                                </div>
                            </div>
                        </div>
                        <img id="preview-bukti" class="preview-image">
                    </div>

                    <div class="button-group">
                        <button type="submit" name="pesan" class="btn-custom btn-primary">
                            <i class="fas fa-shopping-cart"></i>
                            Buat Pesanan
                        </button>
                        <a href="index.php?page=home" class="btn-custom btn-outline">
                            <i class="fas fa-arrow-left"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </form>

            <?php if($success_message): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= $success_message ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function previewBukti(event) {
    const output = document.getElementById('preview-bukti');
    const file = event.target.files[0];
    
    if (file) {
        output.src = URL.createObjectURL(file);
        output.style.display = 'block';
        
        // Update display text
        const display = document.querySelector('.file-input-display .file-input-content');
        display.innerHTML = `
            <i class="fas fa-check-circle fa-2x mb-2" style="color: var(--success-color);"></i>
            <div><strong>File berhasil dipilih</strong></div>
            <small>${file.name}</small>
        `;
    }
}

// Calculate total price when quantity changes
document.getElementById('jumlah').addEventListener('input', function() {
    const quantity = parseInt(this.value) || 0;
    const price = <?= $item['harga'] ?>;
    const total = quantity * price;

    document.getElementById('total-harga').innerText = 
        "Rp " + total.toLocaleString('id-ID');
});
</script>

</body>
</html>