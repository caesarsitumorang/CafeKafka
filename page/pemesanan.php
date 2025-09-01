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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
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

.container-fluid {
    padding: 2rem 1rem;
}

.main-card {
    background: var(--white);
    border-radius: var(--border-radius-xl);
    box-shadow: var(--shadow-lg);
    border: none;
    overflow: hidden;
    max-width: 1200px;
    margin: 0 auto;
}

.image-section {
    position: relative;
    background: var(--gray-200);
    min-height: 400px;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    min-height: 400px;
}

.content-section {
    padding: 2.5rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 400px;
}

.product-header {
    margin-bottom: 2rem;
}

.product-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 1rem;
    line-height: 1.2;
}

.product-description {
    color: var(--gray-600);
    font-size: 1rem;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.product-info {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.info-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    font-size: 0.9rem;
}

.price-badge {
    background: linear-gradient(135deg, var(--secondary-color), #5dade2);
    color: var(--white);
}

.stock-badge {
    background: linear-gradient(135deg, var(--success-color), #58d68d);
    color: var(--white);
}

.payment-info {
    background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
    padding: 1.5rem;
    border-radius: var(--border-radius-lg);
    margin-bottom: 2rem;
    border: 1px solid var(--gray-300);
}

.payment-info h6 {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.payment-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.payment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
}

.payment-label {
    color: var(--gray-600);
    font-weight: 500;
}

.payment-value {
    color: var(--primary-color);
    font-weight: 600;
    background: var(--white);
    padding: 0.25rem 0.75rem;
    border-radius: var(--border-radius);
    border: 1px solid var(--gray-300);
}

.form-section {
    margin-top: auto;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-control {
    border: 2px solid var(--gray-300);
    border-radius: var(--border-radius);
    padding: 0.875rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: var(--white);
}

.form-control:focus {
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    outline: none;
}

.file-input-wrapper {
    position: relative;
    display: block;
}

.file-input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-input-display {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    border: 2px dashed var(--gray-300);
    border-radius: var(--border-radius);
    background: var(--gray-100);
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-input-display:hover {
    border-color: var(--secondary-color);
    background: rgba(52, 152, 219, 0.05);
}

.file-input-content {
    text-align: center;
    color: var(--gray-600);
}

.preview-image {
    max-width: 100%;
    max-height: 200px;
    margin-top: 1rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    display: none;
}

.button-group {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-custom {
    padding: 1rem 2rem;
    border-radius: var(--border-radius-lg);
    font-weight: 600;
    font-size: 1rem;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, var(--secondary-color), #5dade2);
    color: var(--white);
    box-shadow: var(--shadow);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2980b9, var(--secondary-color));
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    color: var(--white);
}

.btn-outline {
    background: var(--white);
    color: var(--primary-color);
    border: 2px solid var(--gray-300);
}

.btn-outline:hover {
    background: var(--primary-color);
    color: var(--white);
    border-color: var(--primary-color);
    text-decoration: none;
}

.alert-custom {
    background: linear-gradient(135deg, #d5f4e6, #a7f3d0);
    color: var(--success-color);
    border: 1px solid #86efac;
    border-radius: var(--border-radius-lg);
    padding: 1rem 1.5rem;
    margin-top: 1.5rem;
    font-weight: 600;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem;
    }
    
    .main-card {
        flex-direction: column;
    }
    
    .content-section {
        padding: 2rem 1.5rem;
        min-height: auto;
    }
    
    .product-title {
        font-size: 1.75rem;
    }
    
    .product-info {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .info-badge {
        justify-content: center;
    }
    
    .payment-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .payment-value {
        align-self: stretch;
        text-align: center;
    }
}

@media (min-width: 769px) {
    .main-card {
        display: flex;
        flex-direction: row;
    }
    
    .image-section {
        flex: 0 0 50%;
    }
    
    .content-section {
        flex: 1;
    }
}
</style>
</head>
<body>

<div class="container-fluid">
    <div class="main-card">
        <!-- Bagian Gambar -->
        <div class="image-section">
            <img src="<?= $item['gambar'] ? $item['gambar'] : 'assets/default.jpg' ?>" 
                 alt="<?= $item['nama'] ?>" 
                 class="product-image">
        </div>

        <!-- Bagian Konten -->
        <div class="content-section">
            <!-- Header Produk -->
            <div class="product-header">
                <h1 class="product-title"><?= htmlspecialchars($item['nama']) ?></h1>
                <p class="product-description"><?= htmlspecialchars($item['deskripsi']) ?></p>
                
                <div class="product-info">
                    <div class="info-badge price-badge">
                        <i class="fas fa-tag"></i>
                        Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                    </div>
                    <div class="info-badge stock-badge">
                        <i class="fas fa-boxes"></i>
                        Stok: <?= $item['stok'] ?>
                    </div>
                </div>
            </div>

            <!-- Info Pembayaran -->
            <div class="payment-info">
                <h6>
                    <i class="fas fa-credit-card"></i>
                    Informasi Pembayaran
                </h6>
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

            <!-- Form Pemesanan -->
            <div class="form-section">
                <form method="post" enctype="multipart/form-data">
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
        <i class="fas fa-money-bill"></i>
        Total Harga
    </label>
    <div id="total-harga" class="total-display" style="font-weight:bold; color:green;">
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
                                    <div>Klik untuk upload gambar</div>
                                    <small>Format: JPG, PNG, GIF</small>
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
                            Kembali ke Beranda
                        </a>
                    </div>
                </form>

                <?php if($success_message): ?>
                    <div class="alert-custom">
                        <i class="fas fa-check-circle"></i>
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>
            </div>
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
            <div>File berhasil dipilih</div>
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